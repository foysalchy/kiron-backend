<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait HasSlugCache
{
    public static function clearSlugCache($companyId, $slug, string $prefix)
    {
        Cache::forget("{$prefix}_{$companyId}_{$slug}");
    }
}
