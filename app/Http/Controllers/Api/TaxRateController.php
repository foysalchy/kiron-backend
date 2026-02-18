<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tax\StoreTaxRateRequest;
use App\Http\Requests\Tax\UpdateTaxRateRequest;
use App\Services\TaxRateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxRateController extends Controller
{
    public function __construct(protected TaxRateService $taxRateService)
    {
    }

    /**
     * Display a listing of tax rates.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'company_id' => $request->query('company_id'),
            'status'     => $request->query('status'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];

        $data = $this->taxRateService->getAllTaxRates($filters);
        return ResponseHelper::success($data, 'Tax rates retrieved successfully');
    }

    /**
     * Store a newly created tax rate.
     */
    public function store(StoreTaxRateRequest $request): JsonResponse
    {
        $data = $this->taxRateService->createTaxRate($request->validated());

        return ResponseHelper::success($data, 'Tax rate created successfully');
    }

    /**
     * Display the specified tax rate.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->taxRateService->getTaxRateById($id);

        return ResponseHelper::success($data, 'Tax rate retrieved successfully');
    }

    /**
     * Update the specified tax rate.
     */
    public function update(UpdateTaxRateRequest $request, int $id): JsonResponse
    {
        $data = $this->taxRateService->updateTaxRate($id, $request->validated());

        return ResponseHelper::success($data, 'Tax rate updated successfully');
    }

    /**
     * Remove the specified tax rate (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->taxRateService->deleteTaxRate($id);
        return ResponseHelper::success(null, 'Tax rate deleted successfully');
    }

    /**
     * Restore the specified soft-deleted tax rate.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->taxRateService->restoreTaxRate($id);

        return ResponseHelper::success($data, 'Tax rate restored successfully');
    }

    /**
     * Permanently delete the specified tax rate.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->taxRateService->forceDeleteTaxRate($id);

        return ResponseHelper::success(null, 'Tax rate permanently deleted');
    }

    /**
     * Toggle the status of the tax rate (Active/Inactive).
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->taxRateService->toggleStatus($id);

        return ResponseHelper::success($data, 'Tax rate status updated successfully');
    }
}
