<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Asset\StoreAssetCategoryRequest;
use App\Http\Requests\Asset\UpdateAssetCategoryRequest;
use App\Services\AssetCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetCategoryController extends Controller
{
    public function __construct(protected AssetCategoryService $assetService)
    {}
    /**
     * Display a listing of asset categories.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->assetService->getAllCategories($request->all());
        return ResponseHelper::success($data, 'Asset categories retrieved successfully');
    }

    /**
     * Store a newly created asset category.
     */
    public function store(StoreAssetCategoryRequest $request): JsonResponse
    {
        $data = $this->assetService->createCategory($request->validated());
        return ResponseHelper::success($data, 'Asset category created successfully', 201);
    }

    /**
     * Display the specified asset category.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->assetService->getCategoryById($id);
        return ResponseHelper::success($data, 'Asset category details retrieved successfully');
    }

    /**
     * Update the specified asset category.
     */
    public function update(int $id, UpdateAssetCategoryRequest $request): JsonResponse
    {
        $data = $this->assetService->updateCategory($id, $request->validated());
        return ResponseHelper::success($data, 'Asset category updated successfully');
    }

    /**
     * Remove the specified asset category (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->assetService->deleteCategory($id);
        return ResponseHelper::success(null, 'Asset category deleted successfully');
    }

    /**
     * Restore a soft-deleted asset category.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->assetService->restoreCategory($id);
        return ResponseHelper::success($data, 'Asset category restored successfully');
    }

    /**
     * Permanently delete an asset category.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->assetService->forceDeleteCategory($id);
        return ResponseHelper::success(null, 'Asset category permanently deleted');
    }

    /**
     * Toggle asset category status (Active/Inactive).
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->assetService->toggleStatus($id);
        return ResponseHelper::success($data, 'Asset category status toggled successfully');
    }
}
