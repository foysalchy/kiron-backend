<?php

namespace App\Services\Saas;


use App\Models\SystemPage;

use Illuminate\Support\Facades\Cache;

class SystemPageService
{
    public static function get(string $pageType, ?int $companyId = null): ?SystemPage
    {
        return Cache::remember(
            "system_page_{$pageType}_{$companyId}",
            now()->addHours(6),
            function () use ($pageType, $companyId) {
                $page = null;

                if ($companyId) {
                    $page = SystemPage::where('page_type', $pageType)
                        ->where('company_id', $companyId)
                        ->whereNotNull('description')
                        ->latest('id')
                        ->first()
                        ?? SystemPage::where('page_type', $pageType)
                            ->where('company_id', $companyId)
                            ->latest('id')
                            ->first();
                }

                return $page ?? SystemPage::where('page_type', $pageType)
                    ->whereNull('company_id')
                    ->latest('id')
                    ->first();
            }
        );
    }
}