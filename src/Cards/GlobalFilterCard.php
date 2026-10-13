<?php

namespace LucaCermenati\NovaGlobalFilters\Cards;

use Laravel\Nova\Card;
use LucaCermenati\NovaGlobalFilters\Filters\GlobalFilter;
use LucaCermenati\NovaGlobalFilters\GlobalFilterManager;

/**
 * The generic card that renders a global filter's inputs and stores its value.
 *
 * @method static static make(\LucaCermenati\NovaGlobalFilters\Filters\GlobalFilter|string $filter)
 */
class GlobalFilterCard extends Card
{
    public $width = 'full';

    public $height = 'dynamic';

    protected GlobalFilter $filter;

    protected ?string $scope = null;

    protected ?string $name = null;

    /**
     * @param  GlobalFilter|string  $filter  a filter instance (registered on the fly) or the key of a registered filter
     */
    public function __construct(GlobalFilter|string $filter)
    {
        parent::__construct();

        $manager = $this->manager();

        if ($filter instanceof GlobalFilter) {
            $manager->register($filter);
            $this->filter = $filter;
        } else {
            $this->filter = $manager->filter($filter);
        }
    }

    public function component(): string
    {
        return 'nova-global-filter';
    }

    /**
     * Isolate this card (and the metrics declaring the same scope) from others.
     */
    public function scope(string $scope): static
    {
        $this->scope = $scope;

        return $this;
    }

    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function filter(): GlobalFilter
    {
        return $this->filter;
    }

    public function uriKey(): string
    {
        return 'global-filter-'.$this->filter->key().'-'.$this->resolvedScope();
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $manager = $this->manager();
        $key = $this->filter->key();
        $scope = $this->resolvedScope();
        $value = $manager->value($key, $scope);

        return array_merge(parent::jsonSerialize(), [
            'uriKey' => $this->uriKey(),
            'filterKey' => $key,
            'name' => $this->name ?? $this->filter->name(),
            'scope' => $scope,
            'inputs' => $this->filter->jsonSerialize()['inputs'],
            'value' => $value === null
                ? array_merge($this->filter->defaults(), $manager->rawValue($key, $scope) ?? [])
                : $this->filter->serialize($value),
            'summary' => $value === null ? null : $this->filter->summary($value),
            'hasValue' => $value !== null,
        ]);
    }

    protected function resolvedScope(): string
    {
        return $this->manager()->store()->scope($this->scope);
    }

    protected function manager(): GlobalFilterManager
    {
        return app(GlobalFilterManager::class);
    }
}
