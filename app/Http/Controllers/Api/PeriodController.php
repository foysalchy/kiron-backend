<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePeriodRequest;
use App\Http\Requests\UpdatePeriodRequest;
use App\Services\PeriodService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PeriodController extends Controller
{
    public function __construct(
        protected PeriodService $periodService
    ) {}

    /**
     * Get all periods with optional filters
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'         => $request->query('status'),
            'period_type_id' => $request->query('period_type_id'),
            'search'         => $request->query('search'),
            'sort_by'        => $request->query('sort_by', 'created_at'),
            'sort_order'     => $request->query('sort_order', 'desc'),
            'per_page'       => $request->query('per_page', 15),
        ];

        $data = $this->periodService->getAllPeriods($filters, true);

        return ResponseHelper::success($data, 'Periods retrieved successfully');
    }

    /**
     * Store a new period
     */
    public function store(StorePeriodRequest $request): JsonResponse
    {
        $period = $this->periodService->createPeriod($request->validated());

        return ResponseHelper::created($period, 'Period created successfully');
    }

    /**
     * Get period by ID
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->periodService->getPeriodById($id);

        return ResponseHelper::success($data, 'Period retrieved successfully');
    }

    /**
     * Update period
     */
    public function update(UpdatePeriodRequest $request, int $id): JsonResponse
    {
        $data = $this->periodService->updatePeriod($id, $request->validated());

        return ResponseHelper::success($data, 'Period updated successfully');
    }

    /**
     * Soft delete period
     */
    public function destroy(int $id): JsonResponse
    {
        $this->periodService->deletePeriod($id);

        return ResponseHelper::success(null, 'Period deleted successfully');
    }

    /**
     * Restore soft deleted period
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->periodService->restorePeriod($id);

        return ResponseHelper::success($data, 'Period restored successfully');
    }

    /**
     * Permanently delete period
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->periodService->forceDeletePeriod($id);

        return ResponseHelper::success(null, 'Period permanently deleted');
    }

    /**
     * Toggle period status
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->periodService->toggleStatus($id);

        return ResponseHelper::success($data, 'Period status updated successfully');
    }
}
