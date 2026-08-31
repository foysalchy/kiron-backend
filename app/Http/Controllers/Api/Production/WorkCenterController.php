<?php

namespace App\Http\Controllers\Api\Production;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\Production\WorkCenterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkCenterController extends Controller
{
    public function __construct(
        protected WorkCenterService $workCenterService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->query('status'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'name'),
            'sort_order' => $request->query('sort_order', 'asc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->workCenterService->getAll($filters, true);
        return ResponseHelper::success($data, 'Work centers retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'machine_name' => 'nullable|string|max:255',
            'capacity_per_day' => 'nullable|numeric|min:0',
            'hourly_cost' => 'nullable|numeric|min:0',
            'operating_hours_per_day' => 'nullable|numeric|min:0|max:24',
            'responsible_person_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $data = $this->workCenterService->create($validated);
        return ResponseHelper::success($data, 'Work center created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->workCenterService->getById($id);
        return ResponseHelper::success($data, 'Work center retrieved successfully');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'machine_name' => 'nullable|string|max:255',
            'capacity_per_day' => 'nullable|numeric|min:0',
            'hourly_cost' => 'nullable|numeric|min:0',
            'operating_hours_per_day' => 'nullable|numeric|min:0|max:24',
            'responsible_person_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $data = $this->workCenterService->update($id, $validated);
        return ResponseHelper::success($data, 'Work center updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->workCenterService->delete($id);
        return ResponseHelper::success(null, 'Work center deleted successfully');
    }
}
