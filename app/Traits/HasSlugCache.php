<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait HasSlugCache
{
    // HasSlugCache.php - update, companyId optional করা
    public static function clearSlugCache($companyId, $slug, string $prefix)
    {
        $suffix = $companyId ? "{$companyId}_{$slug}" : $slug;
        Cache::forget("{$prefix}_{$suffix}");
    }
}
