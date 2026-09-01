<?php

namespace App\Http\Controllers\Api\Project;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\Project\ProjectTimeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectTimeController extends Controller
{
    public function __construct(
        protected ProjectTimeService $timeService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['project_id', 'task_id', 'employee_id', 'date_from', 'date_to']);
        $perPage = (int)$request->get('per_page', 50);

        $entries = $this->timeService->getTimeEntries($filters, $perPage);
        return ResponseHelper::success($entries, 'Time entries retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'phase_id' => 'nullable|exists:project_phases,id',
            'task_id' => 'nullable|exists:project_tasks,id',
            'employee_id' => 'nullable|exists:employees,id',
            'date' => 'required|date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'duration_minutes' => 'nullable|integer',
            'is_billable' => 'nullable|boolean',
            'cost_rate' => 'nullable|numeric',
            'billable_rate' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        $entry = $this->timeService->logTime($validated);
        return ResponseHelper::created($entry, 'Time logged successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->timeService->deleteTimeEntry($id);
        return ResponseHelper::success(null, 'Time entry deleted');
    }
}
