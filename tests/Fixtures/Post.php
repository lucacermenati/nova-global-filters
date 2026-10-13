<?php

namespace LucaCermenati\NovaGlobalFilters\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $guarded = [];

    /**
     * Create a post published/created N days ago.
     */
    public static function daysAgo(int $days, array $attributes = []): self
    {
        $date = now()->subDays($days);

        return static::query()->create(array_merge([
            'created_at' => $date,
            'updated_at' => $date,
            'published_at' => $date,
            'country' => 'NL',
            'status' => 'published',
            'amount' => 10,
        ], $attributes));
    }
}
