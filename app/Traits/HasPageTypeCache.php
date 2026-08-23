<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use App\Models\Company;

trait HasPageTypeCache
{
    public static function clearPageTypeCache(string $pageType, ?int $companyId)
    {
        if ($companyId) {
            Cache::forget("system_page_{$pageType}_{$companyId}");
            return;
        }

        // company_id null মানে super admin/global data change হয়েছে
        // এটা global cache + fallback হিসেবে ব্যবহারকারী প্রতিটা company এর cache কে affect করতে পারে
        Cache::forget("system_page_{$pageType}_");

        Company::pluck('id')->each(function ($id) use ($pageType) {
            Cache::forget("system_page_{$pageType}_{$id}");
        });
    }
}
