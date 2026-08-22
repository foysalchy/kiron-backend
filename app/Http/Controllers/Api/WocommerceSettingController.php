<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWocommerceSettingRequest;
use App\Http\Requests\UpdateWocommerceSettingRequest;
use App\Services\WocommerceSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WocommerceSettingController extends Controller
{
    public function __construct(
        protected WocommerceSettingService $wocommerceService
    ) {}

    /**
     * Display a listing of WooCommerce settings.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'     => $request->query('status'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];

        $data = $this->wocommerceService->getAllSettings($filters, true);

        return ResponseHelper::success($data, 'WooCommerce settings retrieved successfully');
    }

    /**
     * Store a newly created WooCommerce setting.
     */
    public function store(StoreWocommerceSettingRequest $request): JsonResponse
    {
        $data = $this->wocommerceService->createSetting($request->validated());

        return ResponseHelper::success($data, 'WooCommerce setting created successfully', 201);
    }

    /**
     * Display the specified WooCommerce setting.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->wocommerceService->getSettingById($id);

        return ResponseHelper::success($data, 'WooCommerce setting retrieved successfully');
    }

    /**
     * Update the specified WooCommerce setting.
     */
    public function update(UpdateWocommerceSettingRequest $request, int $id): JsonResponse
    {
        $data = $this->wocommerceService->updateSetting($id, $request->validated());

        return ResponseHelper::success($data, 'WooCommerce setting updated successfully');
    }

    /**
     * Remove the specified WooCommerce setting (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->wocommerceService->deleteSetting($id);

        return ResponseHelper::success(null, 'WooCommerce setting deleted successfully');
    }

    /**
     * Restore a soft-deleted WooCommerce setting.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->wocommerceService->restoreSetting($id);

        return ResponseHelper::success($data, 'WooCommerce setting restored successfully');
    }

    /**
     * Permanently delete a WooCommerce setting.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->wocommerceService->forceDeleteSetting($id);

        return ResponseHelper::success(null, 'WooCommerce setting permanently deleted');
    }

    /**
     * Toggle WooCommerce integration status.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->wocommerceService->toggleStatus($id);

        return ResponseHelper::success($data, 'WooCommerce status updated successfully');
    }
    public function toggleProductSync(int $id): JsonResponse
    {
        $data = $this->wocommerceService->toggleProductSync($id);

        return ResponseHelper::success($data, 'WooCommerce product sync updated successfully');
    }

    public function toggleOrderSync(int $id): JsonResponse
    {
        $data = $this->wocommerceService->toggleOrderSync($id);

        return ResponseHelper::success($data, 'WooCommerce order sync updated successfully');
    }
    public function import(Request $request, int $id): JsonResponse
    {
        $data = $this->wocommerceService->importProducts($id, $request);

        return ResponseHelper::success($data, 'WooCommerce products fetched successfully.');
    }
    public function importProduct(Request $request): JsonResponse
    {
        $data = $this->wocommerceService->importProductInDB($request);

        return ResponseHelper::success($data, 'WooCommerce products imported successfully.');
    }

    
}
