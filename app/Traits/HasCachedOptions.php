<?php
// app/Traits/HasCachedOptions.php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use App\Enums\Status;

trait HasCachedOptions
{
    public static function optionsCacheKey($companyId)
    {
        $modelName = strtolower(class_basename(static::class));
        return "{$modelName}_options_company_{$companyId}";
    }

    public static function getCachedOptions($companyId, array $columns)
    {
        $selectColumns = array_unique(array_merge($columns, ['status']));

        return Cache::remember(
            self::optionsCacheKey($companyId),
            now()->addHours(24),
            fn() => self::query()
                ->select($selectColumns)
                ->orderBy('name')
                ->get()
        );
    }

    public static function getActiveCachedOptions($companyId, array $columns)
    {
        return self::getCachedOptions($companyId, $columns)
            ->where('status', Status::Active->value)
            ->values();
    }

    public static function clearOptionsCache($companyId)
    {
        Cache::forget(self::optionsCacheKey($companyId));
    }
}
