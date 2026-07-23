<?php


namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait HasSaasCache
{
    public static function clearSaasCache(string $key)
    {
        Cache::forget($key);
    }

    public static function clearPaginatedSaasCache(string $prefix, int $maxPages = 20)
    {
        for ($i = 1; $i <= $maxPages; $i++) {
            Cache::forget("{$prefix}_page_{$i}");
        }
    }
}
