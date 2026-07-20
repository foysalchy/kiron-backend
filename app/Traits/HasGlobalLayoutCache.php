<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use App\Models\Company;

trait HasGlobalLayoutCache
{
    public static function clearGlobalLayoutCache($companyId, string $section)
    {
        $suffix = $companyId ?: 'global';
        Cache::forget("layout_{$section}_{$suffix}");

        if (!$companyId) {
            Company::pluck('id')->each(function ($id) use ($section) {
                Cache::forget("layout_{$section}_{$id}");
            });
        }
    }
}
