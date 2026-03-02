<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Asset\StoreAssetDepreciationRequest;
use App\Http\Requests\Asset\UpdateAssetDepreciationRequest;
use App\Services\AssetDepreciationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetDepreciationController extends Controller
{
    public function __construct(protected AssetDepreciationService $depreciationService)
    {}
    /**
     * Display a listing of asset depreciations.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->depreciationService->getAllDepreciations($request->all());
        return ResponseHelper::success($data, 'Asset depreciations retrieved successfully');
    }

    /**
     * Store a newly created asset depreciation.
     */
    public function store(StoreAssetDepreciationRequest $request): JsonResponse
    {
        $data = $this->depreciationService->createDepreciation($request->validated());
        return ResponseHelper::success($data, 'Asset depreciation created successfully', 201);
    }

    /**
     * Display the specified asset depreciation.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->depreciationService->getDepreciationById($id);
        return ResponseHelper::success($data, 'Asset depreciation details retrieved successfully');
    }

    /**
     * Update the specified asset depreciation.
     */
    public function update(int $id, UpdateAssetDepreciationRequest $request): JsonResponse
    {
        $data = $this->depreciationService->updateDepreciation($id, $request->validated());
        return ResponseHelper::success($data, 'Asset depreciation updated successfully');
    }

    /**
     * Remove the specified asset depreciation (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->depreciationService->deleteDepreciation($id);
        return ResponseHelper::success(null, 'Asset depreciation deleted successfully');
    }

    /**
     * Restore a soft-deleted asset depreciation.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->depreciationService->restoreDepreciation($id);
        return ResponseHelper::success($data, 'Asset depreciation restored successfully');
    }
    /**
     * Permanently delete an asset depreciation.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->depreciationService->forceDeleteDepreciation($id);
        return ResponseHelper::success(null, 'Asset depreciation permanently deleted');
    }

    /**
     * Toggle asset depreciation status (Active/Inactive).
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->depreciationService->toggleStatus($id);
        return ResponseHelper::success($data, 'Asset depreciation status toggled successfully');
    }
}
