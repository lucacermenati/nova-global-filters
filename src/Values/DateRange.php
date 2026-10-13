<?php

namespace LucaCermenati\NovaGlobalFilters\Values;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * The parsed value of a DateRangeFilter. Either bound may be open.
 */
final class DateRange
{
    public function __construct(
        public readonly ?CarbonImmutable $start = null,
        public readonly ?CarbonImmutable $end = null,
    ) {}

    /**
     * @param  array{start?: string|null, end?: string|null}  $raw
     */
    public static function fromArray(array $raw, ?string $timezone = null): self
    {
        $timezone ??= config('app.timezone');

        $parse = fn (?string $date) => filled($date)
            ? CarbonImmutable::createFromFormat('Y-m-d', $date, $timezone)->startOfDay()
            : null;

        return new self($parse($raw['start'] ?? null), $parse($raw['end'] ?? null));
    }

    public function isEmpty(): bool
    {
        return $this->start === null && $this->end === null;
    }

    public function startOfDay(): ?CarbonImmutable
    {
        return $this->start?->startOfDay();
    }

    public function endOfDay(): ?CarbonImmutable
    {
        return $this->end?->endOfDay();
    }

    public function contains(CarbonInterface $date): bool
    {
        if ($this->start && $date->lt($this->startOfDay())) {
            return false;
        }

        if ($this->end && $date->gt($this->endOfDay())) {
            return false;
        }

        return true;
    }

    /**
     * @return array{start: string|null, end: string|null}
     */
    public function toArray(): array
    {
        return [
            'start' => $this->start?->format('Y-m-d'),
            'end' => $this->end?->format('Y-m-d'),
        ];
    }
}
