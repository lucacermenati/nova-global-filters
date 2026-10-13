<?php

namespace LucaCermenati\NovaGlobalFilters\Tests\Fixtures;

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
