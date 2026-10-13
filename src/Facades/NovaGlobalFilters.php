<?php

namespace LucaCermenati\NovaGlobalFilters\Facades;

use Illuminate\Support\Facades\Facade;
use LucaCermenati\NovaGlobalFilters\GlobalFilterManager;

/**
 * @method static \LucaCermenati\NovaGlobalFilters\GlobalFilterManager register(\LucaCermenati\NovaGlobalFilters\Filters\GlobalFilter|array $filters)
 * @method static \LucaCermenati\NovaGlobalFilters\Filters\GlobalFilter filter(string $key)
 * @method static bool has(string $key)
 * @method static \Illuminate\Support\Collection filters()
 * @method static \LucaCermenati\NovaGlobalFilters\Registry\FilterRegistry registry()
 * @method static \LucaCermenati\NovaGlobalFilters\Storage\FilterStore store()
 * @method static array|null rawValue(string $key, ?string $scope = null)
 * @method static mixed value(string $key, ?string $scope = null)
 * @method static bool hasValue(string $key, ?string $scope = null)
 * @method static void set(string $key, array $raw, ?string $scope = null)
 * @method static void clear(string $key, ?string $scope = null)
 * @method static string|null summary(string $key, ?string $scope = null)
 *
 * @see GlobalFilterManager
 */
class NovaGlobalFilters extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return GlobalFilterManager::class;
    }
}
