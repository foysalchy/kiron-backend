<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use App\Services\PositionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function __construct(
        protected PositionService $positionService
    )
    {}
   /**
     * Get all positions with filters
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'pay_roll_id' => $request->query('pay_roll_id'),
            'type'        => $request->query('type'),
            'status'      => $request->query('status'),
            'search'      => $request->query('search'),
            'sort_by'     => $request->query('sort_by', 'created_at'),
            'sort_order'  => $request->query('sort_order', 'desc'),
            'per_page'    => $request->query('per_page', 15),
        ];

        $positions = $this->positionService->getAllPositions($filters, true);

        return ResponseHelper::success($positions, 'Positions retrieved successfully');
    }
    /**
     * Create a new position
     */
    public function store(StorePositionRequest $request): JsonResponse
    {
        $position = $this->positionService->createPosition($request->validated());

        return ResponseHelper::created($position, 'Position created successfully');
    }
    /**
     * Show single position details
     */
    public function show(int $id): JsonResponse
    {
        $position = $this->positionService->getPositionById($id);

        return ResponseHelper::success($position, 'Position retrieved successfully');
    }

    /**
     * Update position details
     */
    public function update(UpdatePositionRequest $request, int $id): JsonResponse
    {
        $position = $this->positionService->updatePosition($id, $request->validated());

        return ResponseHelper::success($position, 'Position updated successfully');
    }

    /**
     * Soft delete position
     */
    public function destroy(int $id): JsonResponse
    {
        $this->positionService->deletePosition($id);

        return ResponseHelper::success(null, 'Position deleted successfully');
    }

    /**
     * Restore soft deleted position
     */
    public function restore(int $id): JsonResponse
    {
        $position = $this->positionService->restorePosition($id);

        return ResponseHelper::success($position, 'Position restored successfully');
    }

    /**
     * Permanent delete
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->positionService->forceDeletePosition($id);

        return ResponseHelper::success(null, 'Position permanently deleted');
    }

    /**
     * Toggle Active/Inactive status
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $position = $this->positionService->toggleStatus($id);

        return ResponseHelper::success($position, 'Position status updated successfully');
    }
}
