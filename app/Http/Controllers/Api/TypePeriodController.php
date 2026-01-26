<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePeriodTypeRequest;
use App\Http\Requests\UpdatePeriodTypeRequest;
use App\Services\PeriodTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TypePeriodController extends Controller
{
    public function __construct(protected PeriodTypeService $periodTypeService) 
    {} 

    /**
     * Get all period types with filters
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

        $data = $this->periodTypeService->getAllPeriodTypes($filters, true);

        return ResponseHelper::success($data, 'Period Types retrieved successfully');
    }

    /**
     * Store a new period type
     */
    public function store(StorePeriodTypeRequest $request): JsonResponse
    {
        $periodType = $this->periodTypeService->createPeriodType($request->validated());

        return ResponseHelper::created($periodType, 'Period Type created successfully');
    }

    /**
     * Show a specific period type
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->periodTypeService->getPeriodTypeById($id);

        return ResponseHelper::success($data, 'Period Type retrieved successfully');
    }

    /**
     * Update period type
     */
    public function update(UpdatePeriodTypeRequest $request, int $id): JsonResponse
    {
        $data = $this->periodTypeService->updatePeriodType($id, $request->validated());

        return ResponseHelper::success($data, 'Period Type updated successfully');
    }

    /**
     * Delete (Soft Delete) period type
     */
    public function destroy(int $id): JsonResponse
    {
        $this->periodTypeService->deletePeriodType($id);

        return ResponseHelper::success(null, 'Period Type deleted successfully');
    }

    /**
     * Restore soft deleted period type
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->periodTypeService->restorePeriodType($id);

        return ResponseHelper::success($data, 'Period Type restored successfully');
    }

    /**
     * Permanently delete period type
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->periodTypeService->forceDeletePeriodType($id);

        return ResponseHelper::success(null, 'Period Type permanently deleted');
    }

    /**
     * Toggle Period Type status (Active/Inactive)
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->periodTypeService->toggleStatus($id);

        return ResponseHelper::success($data, 'Period Type status updated successfully');
    }
}
