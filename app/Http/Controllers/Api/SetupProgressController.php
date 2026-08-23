<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\DomainSetup;
use App\Models\SystemPage;
use App\Models\Market;
use App\Models\FooterCode;
use App\Models\CustomerPaymentMethod;
use App\Models\CourierCheckHistory;
use App\Models\WocommerceSetting;
use App\Models\AiSetting;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SetupProgressController extends Controller
{
    /**
     * Get the setup progress for the authenticated user's company.
     */
    public function getProgress(Request $request): JsonResponse
    {
        $user = auth()->user();
        $company = $user->company;

        if (!$company) {
            return ResponseHelper::error('No company associated with user.', 404);
        }

        $companyId = $company->id;
        // Add 1 minute to account for initial demo data seeding time
        $threshold = $company->created_at->copy()->addMinutes(1);

        // Setting: SiteSettings
        $setting = SiteSetting::where('company_id', $companyId)
            ->where('updated_at', '>', $threshold)
            ->exists();

        // Theme: DomainSetup (has template_name updated)
        $theme = DomainSetup::where('company_id', $companyId)
            ->whereNotNull('template_name')
            ->where('updated_at', '>', $threshold)
            ->exists();

        // Domain: DomainSetup (has custom domain configured)
        $domain = DomainSetup::where('company_id', $companyId)
            ->where(function($q) {
                $q->whereNotNull('domain_name')
                  ->orWhere('is_custom_domain', 1);
            })
            ->where('updated_at', '>', $threshold)
            ->exists();

        // Meta Setting: SystemPage
        $meta_setting = SystemPage::where('company_id', $companyId)
            ->where('updated_at', '>', $threshold)
            ->exists();

        // Marketing: Market tools or Footer code updated
        $marketing = Market::where('company_id', $companyId)
            ->where('updated_at', '>', $threshold)
            ->exists() 
            || 
            FooterCode::where('company_id', $companyId)
            ->where('updated_at', '>', $threshold)
            ->exists();

        // Integration: Couriers, WooCommerce, AI, or non-manual Payment Methods
        $integration = CourierCheckHistory::where('company_id', $companyId)
            ->where('updated_at', '>', $threshold)
            ->exists()
            ||
            WocommerceSetting::where('company_id', $companyId)
            ->where('updated_at', '>', $threshold)
            ->exists()
            ||
            AiSetting::where('company_id', $companyId)
            ->where('updated_at', '>', $threshold)
            ->exists()
            ||
            CustomerPaymentMethod::where('company_id', $companyId)
            ->where('type', '!=', 'Manual') // Exclude the default Cash on Delivery which is Manual
            ->where('updated_at', '>', $threshold)
            ->exists();

        // Add Your First Product
        $product = Product::where('company_id', $companyId)
            ->where('updated_at', '>', $threshold)
            ->exists();

        $all_completed = $setting && $theme && $domain && $meta_setting && $marketing && $integration && $product;

        $progress = [
            'setting' => $setting,
            'theme' => $theme,
            'domain' => $domain,
            'meta_setting' => $meta_setting,
            'marketing' => $marketing,
            'integration' => $integration,
            'product' => $product,
            'all_completed' => $all_completed
        ];

        return ResponseHelper::success($progress, 'Setup progress retrieved successfully');
    }
}
