<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Asset\StoreAssetPurchaseRequest;
use App\Http\Requests\Asset\UpdateAssetPurchaseRequest;
use App\Services\AssetPurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetPurchaseController extends Controller
{
    public function __construct(protected AssetPurchaseService $purchaseService)
    {}
    /**
     * Display a listing of asset purchases.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->purchaseService->getAllPurchases($request->all());
        return ResponseHelper::success($data, 'Asset purchases retrieved successfully');
    }

    /**
     * Store a newly created asset purchase.
     */
    public function store(StoreAssetPurchaseRequest $request): JsonResponse
    {
        $data = $this->purchaseService->createPurchase($request->validated());
        return ResponseHelper::success($data, 'Asset purchase created successfully', 201);
    }

    /**
     * Display the specified asset purchase.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->purchaseService->getPurchaseById($id);
        return ResponseHelper::success($data, 'Asset purchase details retrieved successfully');
    }

    /**
     * Update the specified asset purchase.
     */
    public function update(int $id, UpdateAssetPurchaseRequest $request): JsonResponse
    {
        $data = $this->purchaseService->updatePurchase($id, $request->validated());
        return ResponseHelper::success($data, 'Asset purchase updated successfully');
    }

    /**
     * Remove the specified asset purchase (Soft Delete).
     */
    public function
    destroy(int $id): JsonResponse
    {
        $this->purchaseService->deletePurchase($id);
        return ResponseHelper::success(null, 'Asset purchase deleted successfully');
    }

    /**
     * Restore a soft-deleted asset purchase.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->purchaseService->restorePurchase($id);
        return ResponseHelper::success($data, 'Asset purchase restored successfully');
    }

    /**
     * Permanently delete an asset purchase.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->purchaseService->forceDeletePurchase($id);
        return ResponseHelper::success(null, 'Asset purchase permanently deleted');
    }

    /**
     * Toggle asset purchase status (Active/Inactive).
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->purchaseService->toggleStatus($id);
        return ResponseHelper::success($data, 'Asset purchase status toggled successfully');
    }
}
