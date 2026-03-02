<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Asset\StoreAssetRequest;
use App\Http\Requests\Asset\UpdateAssetRequest;
use App\Services\AssetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    public function __construct(protected AssetService $assetService)
    {}
    /**
     * Display a listing of assets with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->assetService->getAllAssets($request->all());
        return ResponseHelper::success($data, 'Assets retrieved successfully');
    }

    /**
     * Store a newly created asset.
     */
    public function store(StoreAssetRequest $request): JsonResponse
    {
        $data = $this->assetService->createAsset($request->validated());
        return ResponseHelper::success($data, 'Asset created successfully', 201);
    }

    /**
     * Display the specified asset.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->assetService->getAssetById($id);
        return ResponseHelper::success($data, 'Asset details retrieved successfully');
    }

    /**
     * Update the specified asset.
     */
    public function update(UpdateAssetRequest $request, int $id): JsonResponse
    {
        $data = $this->assetService->updateAsset($id, $request->validated());
        return ResponseHelper::success($data, 'Asset updated successfully');
    }
    /**
     * Remove the specified asset (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->assetService->deleteAsset($id);
        return ResponseHelper::success(null, 'Asset deleted successfully');
    }

    /**
     * Restore a soft-deleted asset.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->assetService->restoreAsset($id);
        return ResponseHelper::success($data, 'Asset restored successfully');
    }

    /**
     * Permanently delete an asset.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->assetService->forceDeleteAsset($id);
        return ResponseHelper::success(null, 'Asset permanently deleted');
    }
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', Rule::in([Status::Active->value, Status::Inactive->value, Status::Disposed->value])]
        ]);

        $data = $this->assetService->updateStatus($id, $request->status);
        return ResponseHelper::success($data, 'Asset status updated successfully');
    }
}
