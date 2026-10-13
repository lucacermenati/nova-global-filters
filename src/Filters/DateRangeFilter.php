<?php

namespace LucaCermenati\NovaGlobalFilters\Filters;

use Illuminate\Contracts\Database\Eloquent\Builder;
use LucaCermenati\NovaGlobalFilters\Inputs\DateRangeInput;
use LucaCermenati\NovaGlobalFilters\Values\DateRange;

/**
 * The shipped reference filter: an optional start and end date that
 * constrains a date column (created_at unless a metric says otherwise).
 */
class DateRangeFilter extends GlobalFilter
{
    public const ATTRIBUTE = 'period';

    public function key(): string
    {
        return $this->key ?? 'date_range';
    }

    public function name(): string
    {
        return $this->name ?? __('Date range');
    }

    public function inputs(): array
    {
        return $this->inputs ?? [
            DateRangeInput::make(self::ATTRIBUTE, __('Period')),
        ];
    }

    public function parse(array $raw): DateRange
    {
        return DateRange::fromArray($raw[self::ATTRIBUTE] ?? []);
    }

    public function isEmpty(mixed $value): bool
    {
        return ! $value instanceof DateRange || $value->isEmpty();
    }

    public function serialize(mixed $value): array
    {
        return [self::ATTRIBUTE => $value instanceof DateRange ? $value->toArray() : ['start' => null, 'end' => null]];
    }

    public function summary(mixed $value): ?string
    {
        if ($this->isEmpty($value)) {
            return null;
        }

        /** @var DateRange $value */
        $start = $value->start?->translatedFormat('j M Y');
        $end = $value->end?->translatedFormat('j M Y');

        return match (true) {
            $start && $end => "{$start} → {$end}",
            (bool) $start => __('From').' '.$start,
            default => __('Until').' '.$end,
        };
    }

    public function defaultOptions(): array
    {
        return $this->defaultOptions ?? ['column' => 'created_at'];
    }

    /**
     * @param  DateRange  $value
     */
    public function apply(Builder $query, mixed $value, array $options = []): void
    {
        $column = $options['column'] ?? 'created_at';

        if ($value->start) {
            $query->where($column, '>=', $value->startOfDay());
        }

        if ($value->end) {
            $query->where($column, '<=', $value->endOfDay());
        }
    }
}
