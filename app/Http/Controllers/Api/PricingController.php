<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\pricing\StorePricingRequest;
use App\Http\Requests\pricing\UpdatePricingRequest;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function __construct(protected PricingService $pricingService) {}
    /**
     * Display a listing of the pricing plans.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();
        $data = $this->pricingService->getAllPricings($filters);

        return ResponseHelper::success($data, 'Pricing plan retrieved successfully');
    }

    /**
     * Store a newly created pricing plan.
     */
    public function store(StorePricingRequest $request): JsonResponse
    {
        $pricing = $this->pricingService->createPricing($request->validated());

        return ResponseHelper::success([
            'message' => 'Pricing plan created successfully.',
            'data'    => $pricing
        ], 201);
    }

    /**
     * Display the specified pricing plan.
     */
    public function show(int $id): JsonResponse
    {
        $pricing = $this->pricingService->getPricingById($id);

       return ResponseHelper::success([
            'message' => 'Pricing plan retrive successfully.',
            'data'    => $pricing
        ]);
    }

    /**
     * Update the specified pricing plan.
     */
    public function update(UpdatePricingRequest $request, int $id): JsonResponse
    {
        $pricing = $this->pricingService->updatePricing($id, $request->validated());

        return ResponseHelper::success([
            'message' => 'Pricing plan updated successfully.',
            'data'    => $pricing
        ]);
    }

    /**
     * Remove the specified pricing plan (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->pricingService->deletePricing($id);

        return ResponseHelper::success([
            'message' => 'Pricing plan moved to trash successfully.'
        ]);
    }

    /**
     * Restore a soft-deleted pricing plan.
     */
    public function restore(int $id): JsonResponse
    {
        $pricing = $this->pricingService->restorePricing($id);

        return ResponseHelper::success([
            'message' => 'Pricing plan restored successfully.',
            'data'    => $pricing
        ]);
    }

    /**
     * Permanently delete a pricing plan.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->pricingService->forceDeletePricing($id);

        return ResponseHelper::success([
            'message' => 'Pricing plan permanently deleted.'
        ]);
    }

    /**
     * Toggle pricing status (Active/Inactive).
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $pricing = $this->pricingService->toggleStatus($id);

        return ResponseHelper::success([
            'message' => 'Pricing status updated successfully.',
            'data'    => $pricing
        ]);
    }
}
