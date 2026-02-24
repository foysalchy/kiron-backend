<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tax\StoreTaxGroupRequest;
use App\Http\Requests\Tax\UpdateTaxGroupRequest;
use App\Services\TaxGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxGroupController extends Controller
{
    public function __construct(protected TaxGroupService $taxGroupService) {}
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

        $data = $this->taxGroupService->getAllTaxGroups($filters);
        return ResponseHelper::success($data, 'Tax rates retrieved successfully');
    }
    /**
     * Store a newly created tax rate.
     */
    public function store(StoreTaxGroupRequest $request): JsonResponse
    {
        $data = $this->taxGroupService->createTaxGroup($request->validated());

        return ResponseHelper::success($data, 'Tax group created successfully');
    }
    /**
     * Display the specified tax group.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->taxGroupService->getTaxGroupById($id);

        return ResponseHelper::success($data, 'Tax group retrieved successfully');
    }
    /**
     * Update the specified tax group.
     */
    public function update(UpdateTaxGroupRequest $request, int $id): JsonResponse
    {
        $data = $this->taxGroupService->updateTaxGroup($id, $request->validated());

        return ResponseHelper::success($data, 'Tax group updated successfully');
    }
    /**
     * Remove the specified tax group (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->taxGroupService->deleteTaxGroup($id);
        return ResponseHelper::success(null, 'Tax group deleted successfully');
    }
    /**
     * Restore the specified soft-deleted tax group.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->taxGroupService->restoreTaxGroup($id);

        return ResponseHelper::success($data, 'Tax group restored successfully');
    }

    /**
     * Permanently delete the specified tax group.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->taxGroupService->forceDeleteTaxGroup($id);

        return ResponseHelper::success(null, 'Tax group permanently deleted');
    }
    /**
     * toggle status.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->taxGroupService->toggleStatus($id);

        return  ResponseHelper::success($data, 'Status updated successfully');
    }
}
