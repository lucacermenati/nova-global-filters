# Nova Global Filters

Session-backed global filters for **Laravel Nova 5** dashboards. Define a filter in PHP,
drop its card on a dashboard, and make any Value, Trend, Partition or Progress metric
respond to it. The value is stored in the session, so it survives navigation, and
applying it refreshes the metrics in place without reloading the page.

Ships with a `DateRangeFilter` (optional start and end date) and a `SectionHeader` card
to group metrics under a title.

## Requirements

- PHP 8.2+
- Laravel 11, 12 or 13
- Laravel Nova 5

## Installation

```bash
composer require lucacermenati/nova-global-filters
```

Optionally publish the config or the translations:

```bash
php artisan vendor:publish --tag=nova-global-filters-config
php artisan vendor:publish --tag=nova-global-filters-lang
```

## Quick start

Register the filters once, typically in `NovaServiceProvider::boot()`:

```php
use LucaCermenati\NovaGlobalFilters\Facades\NovaGlobalFilters;
use LucaCermenati\NovaGlobalFilters\Filters\DateRangeFilter;

NovaGlobalFilters::register([
    DateRangeFilter::make(),
]);
```

Put the card on a dashboard:

```php
use LucaCermenati\NovaGlobalFilters\Cards\GlobalFilterCard;
use LucaCermenati\NovaGlobalFilters\Cards\SectionHeader;

public function cards(): array
{
    return [
        GlobalFilterCard::make('date_range'),

        SectionHeader::make('Users'),
        (new TotalUsers)->width('1/2'),
        (new NewUsersPerDay)->width('1/2'),
    ];
}
```

Make a metric respond to it:

```php
use Laravel\Nova\Metrics\Value;
use LucaCermenati\NovaGlobalFilters\Metrics\RespondsToGlobalFilters;

class TotalUsers extends Value
{
    use RespondsToGlobalFilters;

    public function globalFilters(): array
    {
        return ['date_range'];
    }

    public function calculate(NovaRequest $request)
    {
        return $this->count($request, $this->applyGlobalFilters($request, User::query()));
    }

    public function ranges(): array
    {
        return []; // the global filter is the only time window
    }
}
```

`applyGlobalFilters()` returns an Eloquent builder with the constraints of every declared
filter that currently has a value. Hand it to any Nova aggregate (`count`, `sum`,
`countByDays`, Partition `count`, Progress `count`, …).

## Defining filters

### Subclassing

```php
use Illuminate\Contracts\Database\Eloquent\Builder;
use LucaCermenati\NovaGlobalFilters\Filters\GlobalFilter;
use LucaCermenati\NovaGlobalFilters\Inputs\SelectInput;

class CountryFilter extends GlobalFilter
{
    public function inputs(): array
    {
        return [
            SelectInput::make('country', 'Country')->options(['NL' => 'Netherlands', 'IT' => 'Italy']),
        ];
    }

    public function defaultOptions(): array
    {
        return ['column' => 'country'];
    }

    public function apply(Builder $query, mixed $value, array $options = []): void
    {
        $query->where($options['column'], $value['country']);
    }
}
```

The key defaults to the snake-cased class name without the `Filter` suffix (`country`).
Override `key()` and `name()` to change that.

### Fluent, without a class

```php
GlobalFilter::make('country', 'Country')
    ->withInputs([SelectInput::make('country')->options(['NL' => 'Netherlands', 'IT' => 'Italy'])])
    ->withDefaultOptions(['column' => 'country'])
    ->applyUsing(fn (Builder $query, array $value, array $options) => $query->where($options['column'], $value['country']));
```

### Inputs

| Input | Produces | Rules |
|---|---|---|
| `DateInput::make('since')` | `'Y-m-d'` or null | `date_format:Y-m-d`, optional `min()`/`max()` |
| `DateRangeInput::make('period')` | `['start' => ?, 'end' => ?]` | end must be on or after start |
| `SelectInput::make('status')->options([...])` | one of the option keys or null | `in:` the keys; `anyLabel()` names the empty option |
| `TextInput::make('q')` | string or null | `string`, `max:255` |
| `BooleanInput::make('only_active')` | `true` or null | unchecked means "not filtering" |

Every input accepts `label()`, `help()`, `placeholder()`, `default()` and extra
`rules(...)`. Posted values are validated against these rules before they are stored.

### Hooks on `GlobalFilter`

| Method | Default | Override when |
|---|---|---|
| `parse(array $raw): mixed` | the raw array without empty entries | you want a value object (see `DateRangeFilter`) |
| `isEmpty(mixed $value): bool` | `blank($value)` | your value object knows when it is inactive |
| `serialize(mixed $value): array` | `(array) $value` | the value object must become the form array again |
| `summary(mixed $value): ?string` | `Label: value, …` | you want a nicer "active filter" text |
| `defaultOptions(): array` | `[]` | metrics may override options such as the column |

## Using filters in metrics

Declare what a metric responds to on the class:

```php
public function globalFilters(): array
{
    return [
        'country',                                   // default options
        'date_range' => ['column' => 'closed_at'],   // per-metric override
    ];
}
```

or on the instance, from the dashboard definition:

```php
(new ClosedTickets)->respondsTo('date_range', ['column' => 'closed_at']),
```

### Raw queries

When a metric cannot go through a Nova aggregate, read the parsed value directly:

```php
public function calculate(NovaRequest $request)
{
    $query = Ticket::whereNotNull('closed_at');

    if ($range = $this->globalFilterValue($request, 'date_range')) {   // DateRange|null
        $query->when($range->start, fn ($q) => $q->where('created_at', '>=', $range->startOfDay()))
              ->when($range->end, fn ($q) => $q->where('created_at', '<=', $range->endOfDay()));
    }

    return $this->result((float) $query->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, closed_at)) as s')->value('s'));
}
```

You can also apply the filter's own logic on any builder:

```php
NovaGlobalFilters::filter('date_range')->apply($query, $range, $this->globalFilterOptions('date_range'));
```

### Value metrics without ranges

A Nova `Value` metric with an empty `ranges()` receives no range from the frontend and
Nova then aggregates the **last day only**. `applyGlobalFilters()` detects this and asks
Nova for all time instead, so the global filters are the only window. Metrics that keep
their `ranges()` get both: Nova's range and the global filters intersect.

Trend metrics always apply Nova's own range window; a global date filter only narrows it.

### Cached metrics

The trait extends Nova's metric cache key with the current filter values, so metrics
using `cacheFor()` refresh when a filter changes. If you override `getCacheKey()`
yourself, append `$this->globalFilterCacheKey($request)` to it.

## Scopes

By default every card and metric shares one value per filter. Give a card and its
metrics a scope to isolate a dashboard:

```php
GlobalFilterCard::make('date_range')->scope('sales'),
(new Revenue)->globalFilterScope('sales'),
```

## Reading values elsewhere

```php
NovaGlobalFilters::value('date_range');          // parsed value or null
NovaGlobalFilters::rawValue('date_range');       // the stored form array or null
NovaGlobalFilters::summary('date_range');        // "10 Jan 2026 → 20 Jan 2026"
NovaGlobalFilters::set('country', ['country' => 'NL']);
NovaGlobalFilters::clear('country');
```

## HTTP API

The card talks to these routes (Nova's `api_middleware`, so the user must be allowed to
view Nova):

```
GET    /nova-vendor/nova-global-filters/filters/{filter}
POST   /nova-vendor/nova-global-filters/filters/{filter}   { ...inputs, scope? }
DELETE /nova-vendor/nova-global-filters/filters/{filter}   { scope? }
```

Each returns `{ filter, scope, value, summary, hasValue }`. Validation errors come back
as a normal 422 response and are shown under the input.

## Config

```php
return [
    'session_prefix' => 'nova-global-filters', // session key: {prefix}.{scope}.{filter}
    'default_scope'  => 'default',
    'middleware'     => null,                  // null = config('nova.api_middleware')
];
```

## Limitations

- Filters must be registered with `NovaGlobalFilters::register()` before the write
  endpoints can resolve them. Passing a filter instance to `GlobalFilterCard::make()`
  registers it too, but only while the dashboard is being rendered.
- No per-filter `canSee` yet: anyone who can view Nova can set any registered filter.
- A `BooleanInput` cannot express "false": unchecked means "do not filter". Use a
  `SelectInput` for a yes/no/any choice.

## Development

```bash
composer install
composer test
composer lint

npm install
npm run dev     # or: npm run prod
```

The compiled assets in `dist/` are committed; rebuild them after changing the Vue files.

## Example application

The [nova-playground](https://github.com/lucacermenati/nova-playground) repository is a
small Laravel Nova application that uses this package end to end: it registers a
`DateRangeFilter` and a fluent select filter, puts the cards on the dashboard and has
Value, Trend and Progress metrics responding to them. Use it as a reference for wiring
the package into your own project.

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

MIT, see [LICENSE](LICENSE).
