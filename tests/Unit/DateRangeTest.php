<?php

use Illuminate\Support\Facades\Validator;
use LucaCermenati\NovaGlobalFilters\Inputs\DateRangeInput;
use LucaCermenati\NovaGlobalFilters\Values\DateRange;

it('parses both bounds', function () {
    $range = DateRange::fromArray(['start' => '2026-01-10', 'end' => '2026-01-20']);

    expect($range->isEmpty())->toBeFalse()
        ->and($range->start->toDateString())->toBe('2026-01-10')
        ->and($range->end->toDateString())->toBe('2026-01-20')
        ->and($range->startOfDay()->format('H:i:s'))->toBe('00:00:00')
        ->and($range->endOfDay()->format('H:i:s'))->toBe('23:59:59')
        ->and($range->toArray())->toBe(['start' => '2026-01-10', 'end' => '2026-01-20']);
});

it('accepts an open start or end', function () {
    expect(DateRange::fromArray(['start' => '2026-01-10'])->end)->toBeNull()
        ->and(DateRange::fromArray(['start' => '2026-01-10'])->isEmpty())->toBeFalse()
        ->and(DateRange::fromArray(['end' => '2026-01-10'])->start)->toBeNull()
        ->and(DateRange::fromArray(['start' => '', 'end' => null])->isEmpty())->toBeTrue()
        ->and(DateRange::fromArray([])->isEmpty())->toBeTrue();
});

it('knows whether a date is inside', function () {
    $range = DateRange::fromArray(['start' => '2026-01-10', 'end' => '2026-01-20']);

    expect($range->contains(now()->setDate(2026, 1, 15)))->toBeTrue()
        ->and($range->contains(now()->setDate(2026, 1, 21)->startOfDay()))->toBeFalse()
        ->and(DateRange::fromArray(['start' => '2026-01-10'])->contains(now()->setDate(2030, 1, 1)))->toBeTrue();
});

it('rejects malformed input and an end before the start through the input rules', function () {
    $rules = DateRangeInput::make('period')->validationRules();

    expect(Validator::make(['period' => ['start' => 'garbage']], $rules)->fails())->toBeTrue()
        ->and(Validator::make(['period' => ['start' => '2026-01-20', 'end' => '2026-01-10']], $rules)->errors()->has('period.end'))->toBeTrue()
        ->and(Validator::make(['period' => ['start' => '2026-01-10', 'end' => '2026-01-10']], $rules)->passes())->toBeTrue()
        ->and(Validator::make(['period' => ['start' => null, 'end' => null]], $rules)->passes())->toBeTrue();
});
