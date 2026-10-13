<?php

use Illuminate\Support\Facades\Cache;
use LucaCermenati\NovaGlobalFilters\Facades\NovaGlobalFilters;
use LucaCermenati\NovaGlobalFilters\Tests\Fixtures\AverageAmountRaw;
use LucaCermenati\NovaGlobalFilters\Tests\Fixtures\CachedCount;
use LucaCermenati\NovaGlobalFilters\Tests\Fixtures\CountPosts;
use LucaCermenati\NovaGlobalFilters\Tests\Fixtures\CountPostsWithRanges;
use LucaCermenati\NovaGlobalFilters\Tests\Fixtures\CountPublished;
use LucaCermenati\NovaGlobalFilters\Tests\Fixtures\PlainCount;
use LucaCermenati\NovaGlobalFilters\Tests\Fixtures\Post;
use LucaCermenati\NovaGlobalFilters\Tests\Fixtures\PostsPerDay;
use LucaCermenati\NovaGlobalFilters\Tests\Fixtures\PostsPerStatus;
use LucaCermenati\NovaGlobalFilters\Tests\Fixtures\PublishedProgress;
use LucaCermenati\NovaGlobalFilters\Tests\Fixtures\SumAmount;
use LucaCermenati\NovaGlobalFilters\Values\DateRange;

require_once __DIR__.'/../Fixtures/Metrics.php';

beforeEach(function () {
    // 1, 5, 20 and 60 days old; amounts 10 each; mixed countries and statuses.
    Post::daysAgo(1, ['country' => 'NL', 'status' => 'published']);
    Post::daysAgo(5, ['country' => 'IT', 'status' => 'draft']);
    Post::daysAgo(20, ['country' => 'NL', 'status' => 'published', 'published_at' => now()->subDays(2)]);
    Post::daysAgo(60, ['country' => 'IT', 'status' => 'published']);
});

function dateRange(int $fromDaysAgo, ?int $toDaysAgo = null, ?string $scope = null): void
{
    NovaGlobalFilters::set('date_range', ['period' => [
        'start' => now()->subDays($fromDaysAgo)->toDateString(),
        'end' => $toDaysAgo === null ? null : now()->subDays($toDaysAgo)->toDateString(),
    ]], $scope);
}

it('counts everything when no filter is active, even without ranges (no silent one-day window)', function () {
    $request = $this->metricRequest();

    expect((new CountPosts)->resolve($request)->value)->toEqual(4)
        ->and($request->input('range'))->toBe('ALL')
        ->and((new SumAmount)->resolve($this->metricRequest())->value)->toEqual(40.0);
});

it('narrows Value metrics by the active date range', function () {
    dateRange(10);

    expect((new CountPosts)->resolve($this->metricRequest())->value)->toEqual(2)
        ->and((new SumAmount)->resolve($this->metricRequest())->value)->toEqual(20.0);

    dateRange(30, 3);

    expect((new CountPosts)->resolve($this->metricRequest())->value)->toEqual(2);
});

it('combines several filters', function () {
    dateRange(30);
    NovaGlobalFilters::set('country', ['country' => 'IT']);

    expect((new CountPosts)->resolve($this->metricRequest())->value)->toEqual(1);
});

it('keeps Nova ranges on metrics that declare them', function () {
    dateRange(90);

    $metric = new CountPostsWithRanges;

    expect($metric->resolve($this->metricRequest(['range' => 7]))->value)->toEqual(2)
        ->and($metric->resolve($this->metricRequest(['range' => 30]))->value)->toEqual(3);
});

it('honours a per-metric column override and dashboard-level respondsTo()', function () {
    dateRange(3);

    expect((new CountPublished)->resolve($this->metricRequest())->value)->toEqual(2)
        ->and((new CountPosts)->respondsTo('date_range', ['column' => 'published_at'])->resolve($this->metricRequest())->value)->toEqual(2)
        ->and((new CountPosts)->globalFilterOptions('date_range'))->toBe(['column' => 'created_at'])
        ->and((new CountPublished)->globalFilterOptions('date_range'))->toBe(['column' => 'published_at']);
});

it('leaves metrics without the trait alone', function () {
    dateRange(3);

    expect((new PlainCount)->resolve($this->metricRequest(['range' => 'ALL']))->value)->toEqual(4);
});

it('narrows Trend, Partition and Progress metrics', function () {
    NovaGlobalFilters::set('country', ['country' => 'NL']);
    dateRange(30);

    $trend = (new PostsPerDay)->resolve($this->metricRequest(['range' => 30]));
    $partition = (new PostsPerStatus)->resolve($this->metricRequest());
    $progress = (new PublishedProgress)->resolve($this->metricRequest());

    expect(array_sum($trend->trend))->toEqual(2)
        ->and($partition->value)->toEqual(['draft' => 1, 'published' => 2])
        ->and($progress->value)->toEqual(2);
});

it('hands raw-query metrics the parsed value', function () {
    dateRange(10);

    $metric = new AverageAmountRaw;

    expect($metric->resolve($this->metricRequest())->value)->toEqual(10.0)
        ->and($metric->seen)->toBeInstanceOf(DateRange::class);
});

it('ignores values stored under another scope', function () {
    dateRange(3, null, 'sales');

    expect((new CountPosts)->resolve($this->metricRequest())->value)->toEqual(4)
        ->and((new CountPosts)->globalFilterScope('sales')->resolve($this->metricRequest())->value)->toEqual(1);
});

it('includes the filter state in the cache key so cached metrics refresh', function () {
    $metric = new CachedCount;

    $before = $metric->getCacheKey($this->metricRequest());
    expect($metric->resolve($this->metricRequest())->value)->toEqual(4);

    NovaGlobalFilters::set('country', ['country' => 'IT']);
    $after = $metric->getCacheKey($this->metricRequest());

    expect($after)->not->toBe($before)
        ->and($metric->resolve($this->metricRequest())->value)->toEqual(2)
        ->and(Cache::has($after))->toBeTrue()
        ->and($metric->getCacheKey($this->metricRequest()))->toBe($after);
});
