<?php

use LucaCermenati\NovaGlobalFilters\Exceptions\UnknownFilterException;
use LucaCermenati\NovaGlobalFilters\Filters\DateRangeFilter;
use LucaCermenati\NovaGlobalFilters\Filters\GlobalFilter;
use LucaCermenati\NovaGlobalFilters\Registry\FilterRegistry;

it('registers single filters and arrays, replacing on the same key', function () {
    $registry = new FilterRegistry;
    $first = GlobalFilter::make('country');
    $second = GlobalFilter::make('country', 'Land');

    $registry->register($first)->register([DateRangeFilter::make(), $second]);

    expect($registry->has('country'))->toBeTrue()
        ->and($registry->get('country'))->toBe($second)
        ->and($registry->all()->keys()->all())->toBe(['country', 'date_range'])
        ->and($registry->get('missing'))->toBeNull();
});

it('throws for unknown keys and can be flushed', function () {
    $registry = (new FilterRegistry)->register(DateRangeFilter::make());

    expect(fn () => $registry->find('nope'))->toThrow(UnknownFilterException::class, '[nope]');

    $registry->flush();

    expect($registry->all())->toBeEmpty();
});
