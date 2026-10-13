<?php

namespace LucaCermenati\NovaGlobalFilters\Tests\Fixtures;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\Progress;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Metrics\Value;
use LucaCermenati\NovaGlobalFilters\Metrics\RespondsToGlobalFilters;

class CountPosts extends Value
{
    use RespondsToGlobalFilters;

    public function globalFilters(): array
    {
        return ['date_range', 'country'];
    }

    public function calculate(NovaRequest $request)
    {
        return $this->count($request, $this->applyGlobalFilters($request, Post::class));
    }

    public function ranges(): array
    {
        return [];
    }

    public function uriKey(): string
    {
        return 'count-posts';
    }
}

class SumAmount extends Value
{
    use RespondsToGlobalFilters;

    public function globalFilters(): array
    {
        return ['date_range'];
    }

    public function calculate(NovaRequest $request)
    {
        return $this->sum($request, $this->applyGlobalFilters($request, Post::query()), 'amount');
    }

    public function ranges(): array
    {
        return [];
    }

    public function uriKey(): string
    {
        return 'sum-amount';
    }
}

class CountPostsWithRanges extends Value
{
    use RespondsToGlobalFilters;

    public function globalFilters(): array
    {
        return ['date_range'];
    }

    public function calculate(NovaRequest $request)
    {
        return $this->count($request, $this->applyGlobalFilters($request, Post::class));
    }

    public function ranges(): array
    {
        return [7 => '7 Days', 30 => '30 Days'];
    }

    public function uriKey(): string
    {
        return 'count-posts-ranged';
    }
}

class CountPublished extends Value
{
    use RespondsToGlobalFilters;

    public function globalFilters(): array
    {
        return ['date_range' => ['column' => 'published_at']];
    }

    public function calculate(NovaRequest $request)
    {
        return $this->count($request, $this->applyGlobalFilters($request, Post::class));
    }

    public function ranges(): array
    {
        return [];
    }

    public function uriKey(): string
    {
        return 'count-published';
    }
}

class PlainCount extends Value
{
    public function calculate(NovaRequest $request)
    {
        return $this->count($request, Post::class);
    }

    public function ranges(): array
    {
        return ['ALL' => 'All'];
    }

    public function uriKey(): string
    {
        return 'plain-count';
    }
}

class PostsPerDay extends Trend
{
    use RespondsToGlobalFilters;

    public function globalFilters(): array
    {
        return ['country'];
    }

    public function calculate(NovaRequest $request)
    {
        return $this->countByDays($request, $this->applyGlobalFilters($request, Post::class));
    }

    public function uriKey(): string
    {
        return 'posts-per-day';
    }
}

class PostsPerStatus extends Partition
{
    use RespondsToGlobalFilters;

    public function globalFilters(): array
    {
        return ['date_range'];
    }

    public function calculate(NovaRequest $request)
    {
        return $this->count($request, $this->applyGlobalFilters($request, Post::class), 'status');
    }

    public function uriKey(): string
    {
        return 'posts-per-status';
    }
}

class PublishedProgress extends Progress
{
    use RespondsToGlobalFilters;

    public function globalFilters(): array
    {
        return ['country'];
    }

    public function calculate(NovaRequest $request)
    {
        return $this->count($request, $this->applyGlobalFilters($request, Post::class), fn ($q) => $q->where('status', 'published'), 100);
    }

    public function uriKey(): string
    {
        return 'published-progress';
    }
}

class AverageAmountRaw extends Value
{
    use RespondsToGlobalFilters;

    public ?object $seen = null;

    public function globalFilters(): array
    {
        return ['date_range'];
    }

    public function calculate(NovaRequest $request)
    {
        $query = Post::query();

        if ($range = $this->globalFilterValue($request, 'date_range')) {
            $this->seen = $range;
            $query->when($range->start, fn ($q) => $q->where('created_at', '>=', $range->startOfDay()))
                ->when($range->end, fn ($q) => $q->where('created_at', '<=', $range->endOfDay()));
        }

        return $this->result((float) $query->avg('amount'));
    }

    public function ranges(): array
    {
        return [];
    }

    public function uriKey(): string
    {
        return 'average-amount-raw';
    }
}

class CachedCount extends Value
{
    use RespondsToGlobalFilters;

    public function globalFilters(): array
    {
        return ['country'];
    }

    public function calculate(NovaRequest $request)
    {
        return $this->count($request, $this->applyGlobalFilters($request, Post::class));
    }

    public function cacheFor()
    {
        return now()->addMinutes(10);
    }

    public function ranges(): array
    {
        return [];
    }

    public function uriKey(): string
    {
        return 'cached-count';
    }
}
