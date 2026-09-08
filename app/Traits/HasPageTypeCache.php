<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use App\Models\Company;

trait HasPageTypeCache
{
// HasPageTypeCache trait
public static function clearPageTypeCache(string $pageType, ?int $companyId)
{
    if ($companyId) {
        Cache::forget("system_page_{$pageType}_{$companyId}");
        if ($pageType === 'home') {
            Cache::forget("home_page_data_{$companyId}");
        }
        return;
    }

    Cache::forget("system_page_{$pageType}_");
    if ($pageType === 'home') {
        Cache::forget("home_page_data_");
    }

    Company::pluck('id')->each(function ($id) use ($pageType) {
        Cache::forget("system_page_{$pageType}_{$id}");
        if ($pageType === 'home') {
            Cache::forget("home_page_data_{$id}");
        }
    });
}
}
