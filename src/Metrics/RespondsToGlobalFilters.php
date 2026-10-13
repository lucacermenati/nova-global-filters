<?php

namespace LucaCermenati\NovaGlobalFilters\Metrics;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use LucaCermenati\NovaGlobalFilters\GlobalFilterManager;

/**
 * Make a Value, Trend, Partition or Progress metric respond to global filters.
 *
 * Declare the filters in globalFilters() on the class and/or with respondsTo()
 * on the instance, then pass the query through applyGlobalFilters() before
 * handing it to Nova's aggregate:
 *
 *     return $this->count($request, $this->applyGlobalFilters($request, User::query()));
 */
trait RespondsToGlobalFilters
{
    /** @var array<string, array<string, mixed>> */
    protected array $respondsToFilters = [];

    protected ?string $globalFilterScope = null;

    /**
     * Filters this metric responds to. List items use the filter's default
     * options; map entries override them, e.g. ['date_range' => ['column' => 'closed_at']].
     *
     * @return array<int|string, string|array<string, mixed>>
     */
    public function globalFilters(): array
    {
        return [];
    }

    /**
     * Declare a filter on the instance, e.g. from the dashboard definition.
     *
     * @param  array<string, mixed>  $options
     */
    public function respondsTo(string $key, array $options = []): static
    {
        $this->respondsToFilters[$key] = array_merge($this->respondsToFilters[$key] ?? [], $options);

        return $this;
    }

    public function globalFilterScope(string $scope): static
    {
        $this->globalFilterScope = $scope;

        return $this;
    }

    /**
     * Apply every declared filter that currently has a value.
     *
     * @param  Builder|Model|class-string<Model>  $model
     */
    public function applyGlobalFilters(NovaRequest $request, Builder|Model|string $model): Builder
    {
        $query = $model instanceof Builder ? $model : (new $model)->newQuery();

        $this->useAllTimeRangeWhenRangeless($request);

        $manager = $this->globalFilterManager();

        foreach ($this->declaredGlobalFilters() as $key => $options) {
            $value = $manager->value($key, $this->globalFilterScope);

            if ($value === null) {
                continue;
            }

            $filter = $manager->filter($key);

            $filter->apply($query, $value, array_merge($filter->defaultOptions(), $options));
        }

        return $query;
    }

    /**
     * The parsed value of a filter (e.g. a DateRange), or null when inactive.
     * For raw queries that cannot go through applyGlobalFilters().
     */
    public function globalFilterValue(NovaRequest $request, string $key): mixed
    {
        return $this->globalFilterManager()->value($key, $this->globalFilterScope);
    }

    /**
     * Options of a declared filter as they would be passed to apply().
     *
     * @return array<string, mixed>
     */
    public function globalFilterOptions(string $key): array
    {
        $filter = $this->globalFilterManager()->filter($key);

        return array_merge($filter->defaultOptions(), $this->declaredGlobalFilters()[$key] ?? []);
    }

    /**
     * Nova computes the metric cache key before calculate(); include the
     * current filter values so cacheFor() metrics refresh on a change.
     */
    public function getCacheKey(NovaRequest $request)
    {
        return parent::getCacheKey($request).'.'.$this->globalFilterCacheKey($request);
    }

    public function globalFilterCacheKey(NovaRequest $request): string
    {
        $manager = $this->globalFilterManager();

        $state = collect($this->declaredGlobalFilters())
            ->map(fn ($options, string $key) => $manager->rawValue($key, $this->globalFilterScope))
            ->all();

        return md5(json_encode([$manager->store()->scope($this->globalFilterScope) => $state]));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function declaredGlobalFilters(): array
    {
        $declared = [];

        foreach ($this->globalFilters() as $key => $options) {
            if (is_int($key)) {
                $declared[$options] = [];
            } else {
                $declared[$key] = $options;
            }
        }

        foreach ($this->respondsToFilters as $key => $options) {
            $declared[$key] = array_merge($declared[$key] ?? [], $options);
        }

        return $declared;
    }

    /**
     * A Value metric without ranges receives no "range" parameter from the
     * frontend, and Nova then silently aggregates the last day only. Ask for
     * all time instead, so the global filters are the only window.
     */
    protected function useAllTimeRangeWhenRangeless(NovaRequest $request): void
    {
        if ($this instanceof Value && count($this->ranges()) === 0 && ! $request->filled('range')) {
            $request->merge(['range' => 'ALL']);
        }
    }

    protected function globalFilterManager(): GlobalFilterManager
    {
        return app(GlobalFilterManager::class);
    }
}
