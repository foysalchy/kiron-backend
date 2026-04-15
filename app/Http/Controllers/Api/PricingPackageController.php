<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\PricingPackageRequest;
use App\Services\PricingPackageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PricingPackageController extends Controller
{
    public function __construct(protected PricingPackageService $pricingPackageService) {}

    /**
     * Display a listing of the pricing packages.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();
        $data = $this->pricingPackageService->getAllPricingPackages($filters);

        return ResponseHelper::success($data, 'Pricing packages retrieved successfully');
    }

    /**
     * Store a newly created pricing package.
     */
    public function store(PricingPackageRequest $request): JsonResponse
    {
        $package = $this->pricingPackageService->createPricingPackage($request->validated());

        return ResponseHelper::success([
            'message' => 'Pricing package created successfully.',
            'data'    => $package,
        ], 201);
    }

    /**
     * Display the specified pricing package.
     */
    public function show(int $id): JsonResponse
    {
        $package = $this->pricingPackageService->getPricingPackageById($id);

        return ResponseHelper::success([
            'message' => 'Pricing package retrieved successfully.',
            'data'    => $package,
        ]);
    }

    /**
     * Update the specified pricing package.
     */
    public function update(PricingPackageRequest $request, int $id): JsonResponse
    {
        $package = $this->pricingPackageService->updatePricingPackage($id, $request->validated());

        return ResponseHelper::success([
            'message' => 'Pricing package updated successfully.',
            'data'    => $package,
        ]);
    }

    /**
     * Remove the specified pricing package (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->pricingPackageService->deletePricingPackage($id);

        return ResponseHelper::success([
            'message' => 'Pricing package moved to trash successfully.',
        ]);
    }

    /**
     * Restore a soft-deleted pricing package.
     */
    public function restore(int $id): JsonResponse
    {
        $package = $this->pricingPackageService->restorePricingPackage($id);

        return ResponseHelper::success([
            'message' => 'Pricing package restored successfully.',
            'data'    => $package,
        ]);
    }

    /**
     * Permanently delete a pricing package.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->pricingPackageService->forceDeletePricingPackage($id);

        return ResponseHelper::success([
            'message' => 'Pricing package permanently deleted.',
        ]);
    }

    /**
     * Toggle pricing package status (Active/Inactive).
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $package = $this->pricingPackageService->toggleStatus($id);

        return ResponseHelper::success([
            'message' => 'Pricing package status updated successfully.',
            'data'    => $package,
        ]);
    }
}