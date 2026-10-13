<?php

use LucaCermenati\NovaGlobalFilters\Facades\NovaGlobalFilters;
use LucaCermenati\NovaGlobalFilters\Storage\FilterStore;
use LucaCermenati\NovaGlobalFilters\Values\DateRange;

it('stores raw values per scope in the session', function () {
    $store = app(FilterStore::class);

    $store->put('date_range', ['period' => ['start' => '2026-01-01', 'end' => null]]);
    $store->put('date_range', ['period' => ['start' => '2026-02-01', 'end' => null]], 'sales');

    expect(session('nova-global-filters.default.date_range'))->toBe(['period' => ['start' => '2026-01-01', 'end' => null]])
        ->and($store->get('date_range'))->toBe(['period' => ['start' => '2026-01-01', 'end' => null]])
        ->and($store->get('date_range', 'sales'))->toBe(['period' => ['start' => '2026-02-01', 'end' => null]])
        ->and($store->get('country'))->toBeNull();

    $store->forget('date_range');

    expect($store->get('date_range'))->toBeNull()
        ->and($store->get('date_range', 'sales'))->not->toBeNull();

    $store->flush('sales');

    expect($store->get('date_range', 'sales'))->toBeNull();
});

it('exposes parsed values and summaries through the manager', function () {
    NovaGlobalFilters::set('date_range', ['period' => ['start' => '2026-01-01', 'end' => '2026-01-31']]);
    NovaGlobalFilters::set('country', ['country' => '']);

    expect(NovaGlobalFilters::value('date_range'))->toBeInstanceOf(DateRange::class)
        ->and(NovaGlobalFilters::summary('date_range'))->toBe('1 Jan 2026 → 31 Jan 2026')
        ->and(NovaGlobalFilters::hasValue('date_range'))->toBeTrue()
        ->and(NovaGlobalFilters::value('country'))->toBeNull()
        ->and(NovaGlobalFilters::hasValue('country'))->toBeFalse()
        ->and(NovaGlobalFilters::rawValue('country'))->toBe(['country' => '']);

    NovaGlobalFilters::clear('date_range');

    expect(NovaGlobalFilters::value('date_range'))->toBeNull();
});

it('uses the configured prefix', function () {
    config()->set('nova-global-filters.session_prefix', 'custom');
    $this->app->forgetInstance(FilterStore::class);

    app(FilterStore::class)->put('country', ['country' => 'NL']);

    expect(session('custom.default.country'))->toBe(['country' => 'NL']);
});
