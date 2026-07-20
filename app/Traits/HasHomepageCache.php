<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait HasHomepageCache
{
    public static function clearHomepageCache($companyId)
    {
        $keys = static::homepageCacheKeys();

        foreach ($keys as $key) {
            Cache::forget("{$key}_{$companyId}");
        }
    }
}
