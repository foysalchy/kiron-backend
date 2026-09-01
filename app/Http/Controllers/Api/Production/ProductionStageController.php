<?php

namespace App\Http\Controllers\Api\Production;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\Production\ProductionStageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionStageController extends Controller
{
    public function __construct(
        protected ProductionStageService $stageService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->query('status'),
            'work_center_id' => $request->query('work_center_id'),
            'search' => $request->query('search'),
            'per_page' => $request->query('per_page', 50),
        ];

        $data = $this->stageService->getAll($filters, false);
        return ResponseHelper::success($data, 'Production stages retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sequence' => 'nullable|integer',
            'work_center_id' => 'nullable|exists:work_centers,id',
            'estimated_duration_minutes' => 'nullable|integer|min:0',
            'assigned_team_or_person' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        $data = $this->stageService->create($validated);
        return ResponseHelper::success($data, 'Production stage created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->stageService->getById($id);
        return ResponseHelper::success($data, 'Production stage retrieved successfully');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'sequence' => 'nullable|integer',
            'work_center_id' => 'nullable|exists:work_centers,id',
            'estimated_duration_minutes' => 'nullable|integer|min:0',
            'assigned_team_or_person' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        $data = $this->stageService->update($id, $validated);
        return ResponseHelper::success($data, 'Production stage updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->stageService->delete($id);
        return ResponseHelper::success(null, 'Production stage deleted successfully');
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:production_stages,id',
            'orders.*.sequence' => 'required|integer',
        ]);

        $this->stageService->reorder($validated['orders']);
        return ResponseHelper::success(null, 'Stages reordered successfully');
    }
}
