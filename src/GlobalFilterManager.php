<?php

namespace LucaCermenati\NovaGlobalFilters;

use Illuminate\Support\Collection;
use LucaCermenati\NovaGlobalFilters\Filters\GlobalFilter;
use LucaCermenati\NovaGlobalFilters\Registry\FilterRegistry;
use LucaCermenati\NovaGlobalFilters\Storage\FilterStore;

/**
 * Entry point behind the NovaGlobalFilters facade: the registry of filter
 * definitions plus the session store of their values.
 */
class GlobalFilterManager
{
    public function __construct(
        protected FilterRegistry $registry,
        protected FilterStore $store,
    ) {}

    /**
     * @param  GlobalFilter|array<int, GlobalFilter>  $filters
     */
    public function register(GlobalFilter|array $filters): static
    {
        $this->registry->register($filters);

        return $this;
    }

    public function filter(string $key): GlobalFilter
    {
        return $this->registry->find($key);
    }

    public function has(string $key): bool
    {
        return $this->registry->has($key);
    }

    /**
     * @return Collection<string, GlobalFilter>
     */
    public function filters(): Collection
    {
        return $this->registry->all();
    }

    public function registry(): FilterRegistry
    {
        return $this->registry;
    }

    public function store(): FilterStore
    {
        return $this->store;
    }

    /**
     * The stored form-shaped array, or null when nothing is stored.
     *
     * @return array<string, mixed>|null
     */
    public function rawValue(string $key, ?string $scope = null): ?array
    {
        return $this->store->get($key, $scope);
    }

    /**
     * The parsed value, or null when nothing is stored or the value is empty.
     */
    public function value(string $key, ?string $scope = null): mixed
    {
        $filter = $this->filter($key);
        $raw = $this->rawValue($key, $scope);

        if ($raw === null) {
            return null;
        }

        $value = $filter->parse($raw);

        return $filter->isEmpty($value) ? null : $value;
    }

    public function hasValue(string $key, ?string $scope = null): bool
    {
        return $this->value($key, $scope) !== null;
    }

    /**
     * Store a (validated) raw value.
     *
     * @param  array<string, mixed>  $raw
     */
    public function set(string $key, array $raw, ?string $scope = null): void
    {
        $this->store->put($this->filter($key)->key(), $raw, $scope);
    }

    public function clear(string $key, ?string $scope = null): void
    {
        $this->store->forget($key, $scope);
    }

    public function summary(string $key, ?string $scope = null): ?string
    {
        $value = $this->value($key, $scope);

        return $value === null ? null : $this->filter($key)->summary($value);
    }
}
