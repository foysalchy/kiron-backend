<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSiteSettingRequest;
use App\Http\Requests\UpdateSiteSettingRequest;
use App\Models\DomainSetup;
use App\Services\SiteSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SiteSettingController extends Controller
{
    public function __construct(
        protected SiteSettingService $siteSettingService
    ) {}

    /**
     * Display a listing of site settings.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'     => $request->query('status'),
            'shop_name'  => $request->query('shop_name'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];

        $data = $this->siteSettingService->getAllSiteSettings($filters, true);

        return ResponseHelper::success($data, 'Site settings retrieved successfully');
    }
    /**
     * Store a newly created site setting.
     */
    public function store(StoreSiteSettingRequest $request): JsonResponse
    {
        $data = $this->siteSettingService->createSiteSetting($request->validated());

        return ResponseHelper::success($data, 'Site setting created successfully', 201);
    }

    /**
     * Display the specified site setting.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->siteSettingService->getSiteSettingById($id);

        return ResponseHelper::success($data, 'Site setting retrieved successfully');
    }

    /**
     * Update the specified site setting.
     */
    public function update(UpdateSiteSettingRequest $request, int $id): JsonResponse

    {

        $data = $this->siteSettingService->updateSiteSetting($id, $request->validated());

        return ResponseHelper::success($data, 'Site setting updated successfully');
    }

    /**
     * Remove the specified site setting (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->siteSettingService->deleteSiteSetting($id);

        return ResponseHelper::success(null, 'Site setting deleted successfully');
    }

    /**
     * Restore a soft-deleted site setting.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->siteSettingService->restoreSiteSetting($id);

        return ResponseHelper::success($data, 'Site setting restored successfully');
    }

    /**
     * Permanently delete a site setting.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->siteSettingService->forceDeleteSiteSetting($id);

        return ResponseHelper::success(null, 'Site setting permanently deleted');
    }
    /**
     * Toggle status
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->siteSettingService->toggleStatus($id);

        return ResponseHelper::success($data, 'Site status updated successfully');
    }
    public function updateInvoiceTemplate(Request $request)
    {
        $validated = $request->validate([
            'template_id'     => 'required|string',
            'primary_color'   => 'nullable|string|max:20',
            'secondary_color' => 'nullable|string|max:20',
        ]);

        try {
            $user = auth()->user();
            $company = $user->company;

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found for this user.'
                ], 404);
            }

            $company->update([
                'invoice_template' => [
                    'id'              => $validated['template_id'],
                    'primary_color'   => $validated['primary_color'] ?? null,
                    'secondary_color' => $validated['secondary_color'] ?? null,
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice settings updated successfully!',
                'data'    => $company->fresh()
            ], 200);
        } catch (\Exception $e) {
            Log::error('Invoice Template Update Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update invoice settings.'
            ], 500);
        }
    }
    public function updateThemeTemplate(Request $request)
    {
        $validated = $request->validate([
            'theme_id'             => 'required|string',
            'primary_color'        => 'nullable|string|max:20',
            'secondary_color'      => 'nullable|string|max:20',
            'primary_text_color'   => 'nullable|string|max:20',
            'secondary_text_color' => 'nullable|string|max:20',
            'card_id'              => 'nullable|string|max:100',
            'is_review'            => 'nullable|boolean',
        ]);

        try {
            $user = auth()->user();
            $company = $user->company;

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found for this user.'
                ], 404);
            }

            $company->update([
                'theme_template' => [
                    'id'                   => $validated['theme_id'],
                    'primary_color'        => $validated['primary_color'] ?? null,
                    'secondary_color'      => $validated['secondary_color'] ?? null,
                    'primary_text_color'   => $validated['primary_text_color'] ?? null,
                    'secondary_text_color' => $validated['secondary_text_color'] ?? null,
                ]
            ]);

            $domain = DomainSetup::where('company_id', $company->id)->first();

            if ($domain) {
                $domain->update([
                    'template_name'          => 'template' . $validated['theme_id'],
                    'product_card_template'  => $validated['card_id'] ?? $domain->product_card_template,
                    'is_review'              => array_key_exists('is_review', $validated)
                        ? $validated['is_review']
                        : $domain->is_review,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Theme settings updated successfully!',
                'data'    => $company->fresh()->load('domainSetup') // 👈 relation load kora holo
            ], 200);
        } catch (\Exception $e) {
            Log::error('Theme Template Update Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update theme settings.'
            ], 500);
        }
    }
}
