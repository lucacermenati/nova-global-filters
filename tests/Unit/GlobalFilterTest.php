<?php

use Illuminate\Contracts\Database\Eloquent\Builder;
use LucaCermenati\NovaGlobalFilters\Filters\DateRangeFilter;
use LucaCermenati\NovaGlobalFilters\Filters\GlobalFilter;
use LucaCermenati\NovaGlobalFilters\Inputs\BooleanInput;
use LucaCermenati\NovaGlobalFilters\Inputs\SelectInput;
use LucaCermenati\NovaGlobalFilters\Tests\Fixtures\Post;
use LucaCermenati\NovaGlobalFilters\Values\DateRange;

it('can be defined fluently without a subclass', function () {
    $filter = GlobalFilter::make('country', 'Country')
        ->withInputs([SelectInput::make('country')->options(['NL' => 'Netherlands', 'IT' => 'Italy'])])
        ->withDefaultOptions(['column' => 'country'])
        ->applyUsing(fn (Builder $q, array $v, array $o) => $q->where($o['column'], $v['country']));

    Post::daysAgo(1, ['country' => 'NL']);
    Post::daysAgo(1, ['country' => 'IT']);

    $query = Post::query();
    $filter->apply($query, $filter->parse(['country' => 'IT']), $filter->defaultOptions());

    expect($filter->key())->toBe('country')
        ->and($filter->name())->toBe('Country')
        ->and($filter->rules())->toBe(['country' => ['nullable', 'in:NL,IT']])
        ->and($query->count())->toBe(1)
        ->and($filter->jsonSerialize()['inputs'][0]['type'])->toBe('select');
});

it('derives the key from the class name and treats all-empty values as inactive', function () {
    $filter = GlobalFilter::make()->withInputs([SelectInput::make('a')->options(['x' => 'X']), BooleanInput::make('b')]);

    expect(DateRangeFilter::make()->key())->toBe('date_range')
        ->and((new class extends GlobalFilter {})->key())->not->toBe('')
        ->and($filter->parse(['a' => '', 'b' => null]))->toBe([])
        ->and($filter->isEmpty($filter->parse(['a' => '', 'b' => null])))->toBeTrue()
        ->and($filter->summary(['a' => 'x', 'b' => true]))->toBe('A: x, B: Yes')
        ->and($filter->defaults())->toBe(['a' => null, 'b' => null]);
});

it('ships a date range filter that constrains the configured column', function () {
    $filter = DateRangeFilter::make();
    $value = $filter->parse(['period' => ['start' => '2026-01-10', 'end' => '2026-01-20']]);

    $query = Post::query();
    $filter->apply($query, $value, ['column' => 'published_at']);

    expect($value)->toBeInstanceOf(DateRange::class)
        ->and($query->toRawSql())->toContain('"published_at" >= \'2026-01-10 00:00:00\'', '"published_at" <= \'2026-01-20 23:59:59\'')
        ->and($filter->summary($value))->toBe('10 Jan 2026 → 20 Jan 2026')
        ->and($filter->summary($filter->parse(['period' => ['start' => '2026-01-10']])))->toBe('From 10 Jan 2026')
        ->and($filter->summary($filter->parse(['period' => ['end' => '2026-01-20']])))->toBe('Until 20 Jan 2026')
        ->and($filter->summary($filter->parse([])))->toBeNull()
        ->and($filter->serialize($value))->toBe(['period' => ['start' => '2026-01-10', 'end' => '2026-01-20']])
        ->and($filter->defaultOptions())->toBe(['column' => 'created_at']);
});
