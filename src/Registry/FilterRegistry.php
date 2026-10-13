<?php

namespace LucaCermenati\NovaGlobalFilters\Registry;

use Illuminate\Support\Collection;
use LucaCermenati\NovaGlobalFilters\Exceptions\UnknownFilterException;
use LucaCermenati\NovaGlobalFilters\Filters\GlobalFilter;

class FilterRegistry
{
    /** @var array<string, GlobalFilter> */
    protected array $filters = [];

    /**
     * Register one or more filters. Registering an existing key replaces it.
     *
     * @param  GlobalFilter|array<int, GlobalFilter>  $filters
     */
    public function register(GlobalFilter|array $filters): static
    {
        foreach (is_array($filters) ? $filters : [$filters] as $filter) {
            $this->filters[$filter->key()] = $filter;
        }

        return $this;
    }

    public function get(string $key): ?GlobalFilter
    {
        return $this->filters[$key] ?? null;
    }

    public function find(string $key): GlobalFilter
    {
        return $this->get($key) ?? throw UnknownFilterException::forKey($key);
    }

    public function has(string $key): bool
    {
        return isset($this->filters[$key]);
    }

    /**
     * @return Collection<string, GlobalFilter>
     */
    public function all(): Collection
    {
        return collect($this->filters);
    }

    public function flush(): static
    {
        $this->filters = [];

        return $this;
    }
}
