<?php

use LucaCermenati\NovaGlobalFilters\Cards\GlobalFilterCard;
use LucaCermenati\NovaGlobalFilters\Cards\SectionHeader;
use LucaCermenati\NovaGlobalFilters\Exceptions\UnknownFilterException;
use LucaCermenati\NovaGlobalFilters\Facades\NovaGlobalFilters;
use LucaCermenati\NovaGlobalFilters\Filters\DateRangeFilter;
use LucaCermenati\NovaGlobalFilters\Filters\GlobalFilter;
use LucaCermenati\NovaGlobalFilters\Inputs\TextInput;

it('serializes the inputs, defaults and empty state', function () {
    $json = DateRangeFilter::make()->card()->jsonSerialize();

    expect($json)->toMatchArray([
        'component' => 'nova-global-filter',
        'width' => 'full',
        'height' => 'dynamic',
        'uriKey' => 'global-filter-date_range-default',
        'filterKey' => 'date_range',
        'name' => 'Date range',
        'scope' => 'default',
        'value' => ['period' => null],
        'summary' => null,
        'hasValue' => false,
    ])->and($json['inputs'][0])->toMatchArray(['type' => 'date-range', 'attribute' => 'period']);
});

it('serializes the stored value and summary, per scope', function () {
    NovaGlobalFilters::set('date_range', ['period' => ['start' => '2026-01-10', 'end' => null]], 'sales');

    $default = GlobalFilterCard::make('date_range')->jsonSerialize();
    $sales = GlobalFilterCard::make('date_range')->scope('sales')->name('Sales period')->jsonSerialize();

    expect($default['hasValue'])->toBeFalse()
        ->and($sales)->toMatchArray([
            'uriKey' => 'global-filter-date_range-sales',
            'name' => 'Sales period',
            'scope' => 'sales',
            'value' => ['period' => ['start' => '2026-01-10', 'end' => null]],
            'summary' => 'From 10 Jan 2026',
            'hasValue' => true,
        ]);
});

it('registers a filter instance passed to the card and rejects unknown keys', function () {
    NovaGlobalFilters::registry()->flush();

    $card = GlobalFilterCard::make(GlobalFilter::make('search')->withInputs([TextInput::make('q')]));

    expect(NovaGlobalFilters::has('search'))->toBeTrue()
        ->and($card->filter()->key())->toBe('search')
        ->and(fn () => GlobalFilterCard::make('missing'))->toThrow(UnknownFilterException::class);
});

it('serializes a section header', function () {
    expect(SectionHeader::make('Users', 'Everyone with an account')->jsonSerialize())->toMatchArray([
        'component' => 'nova-section-header',
        'width' => 'full',
        'height' => 'dynamic',
        'uriKey' => 'section-header-users',
        'title' => 'Users',
        'subtitle' => 'Everyone with an account',
    ]);
});
