<?php

namespace App\Http\Controllers\Api\Saas;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Saas\StoreMasterFeatureRequest;
use App\Http\Requests\Saas\UpdateMasterFeatureRequest;
use App\Services\Saas\MasterFeatureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterFeatureController extends Controller
{
    public function __construct(protected MasterFeatureService $featureService)
    {}

    /**
     * list all master features with optional filters (placement, status, search, pagination)
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'placement'  => $request->query('placement'), // ১=ফিচার, ২=বেনিফিট
            'status'     => $request->query('status'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];

        $data = $this->featureService->getAllFeatures($filters);
        return ResponseHelper::success($data, 'Master features retrieved successfully');
    }

    /**
     * Create a new master feature
     */
    public function store(StoreMasterFeatureRequest $request): JsonResponse
    {
        $data = $this->featureService->createFeature($request->validated());
        return ResponseHelper::success($data, 'Master feature created successfully');
    }

    /**
     * Get details of a specific master feature
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->featureService->getFeatureById($id);
        return ResponseHelper::success($data, 'Master feature details retrieved');
    }

    /**
     * Update a master feature
     */
    public function update(UpdateMasterFeatureRequest $request, int $id): JsonResponse
    {
        $data = $this->featureService->updateFeature($id, $request->validated());
        return ResponseHelper::success($data, 'Master feature updated successfully');
    }

    /**
     * Soft delete a master feature
     */
    public function destroy(int $id): JsonResponse
    {
        $this->featureService->deleteFeature($id);
        return ResponseHelper::success(null, 'Master feature deleted successfully');
    }

    /**
     * Restore a deleted master feature
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->featureService->restoreFeature($id);
        return ResponseHelper::success($data, 'Master feature restored successfully');
    }

    /**
     * Permanent delete
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->featureService->forceDeleteFeature($id);
        return ResponseHelper::success(null, 'Master feature permanently deleted');
    }

    /**
     * Toggle active/inactive status
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->featureService->toggleStatus($id);
        return ResponseHelper::success($data, 'Status updated successfully');
    }
}
