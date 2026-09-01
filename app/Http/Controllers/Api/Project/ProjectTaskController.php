<?php

namespace App\Http\Controllers\Api\Project;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\Project\ProjectTaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectTaskController extends Controller
{
    public function __construct(
        protected ProjectTaskService $taskService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'project_id',
            'phase_id',
            'milestone_id',
            'status',
            'priority',
            'assigned_to',
            'quick_filter',
            'search',
        ]);
        $perPage = (int)$request->get('per_page', 50);

        $tasks = $this->taskService->getTasks($filters, $perPage);
        return ResponseHelper::success($tasks, 'Tasks retrieved successfully');
    }

    public function show(int $id): JsonResponse
    {
        $task = $this->taskService->getTaskById($id);
        return ResponseHelper::success($task, 'Task retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'phase_id' => 'nullable|exists:project_phases,id',
            'milestone_id' => 'nullable|exists:project_milestones,id',
            'parent_task_id' => 'nullable|exists:project_tasks,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:employees,id',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric',
            'actual_hours' => 'nullable|numeric',
            'estimated_cost' => 'nullable|numeric',
            'tags' => 'nullable|array',
        ]);

        $task = $this->taskService->createTask($validated);
        return ResponseHelper::created($task, 'Task created successfully');
    }

    public function bulkStore(Request $request): JsonResponse
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'tasks_text' => 'required|string',
            'phase_id' => 'nullable|exists:project_phases,id',
            'parent_task_id' => 'nullable|exists:project_tasks,id',
        ]);

        $created = $this->taskService->createBulkTasks(
            (int)$request->project_id,
            $request->tasks_text,
            $request->phase_id ? (int)$request->phase_id : null,
            $request->parent_task_id ? (int)$request->parent_task_id : null
        );

        return ResponseHelper::created($created, 'Bulk tasks created successfully');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'phase_id' => 'nullable|exists:project_phases,id',
            'milestone_id' => 'nullable|exists:project_milestones,id',
            'parent_task_id' => 'nullable|exists:project_tasks,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:employees,id',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric',
            'actual_hours' => 'nullable|numeric',
            'estimated_cost' => 'nullable|numeric',
            'actual_cost' => 'nullable|numeric',
            'is_completed' => 'nullable|boolean',
            'complete_subtasks' => 'nullable|boolean',
            'tags' => 'nullable|array',
            'sort_order' => 'nullable|integer',
        ]);

        $task = $this->taskService->updateTask($id, $validated);
        return ResponseHelper::success($task, 'Task updated successfully');
    }

    public function toggleComplete(int $id): JsonResponse
    {
        $task = $this->taskService->toggleComplete($id);
        return ResponseHelper::success($task, 'Task status updated');
    }

    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:project_tasks,id',
            'orders.*.sort_order' => 'required|integer',
            'orders.*.status' => 'nullable|string',
            'orders.*.phase_id' => 'nullable',
            'orders.*.parent_task_id' => 'nullable',
        ]);

        $this->taskService->reorderTasks($request->orders);
        return ResponseHelper::success(null, 'Tasks reordered successfully');
    }

    public function startTimer(int $id): JsonResponse
    {
        $task = $this->taskService->startTimer($id);
        return ResponseHelper::success($task, 'Task timer started');
    }

    public function stopTimer(int $id): JsonResponse
    {
        $task = $this->taskService->stopTimer($id);
        return ResponseHelper::success($task, 'Task timer stopped and logged');
    }

    public function statusHistories(int $id): JsonResponse
    {
        $task = $this->taskService->getTaskById($id);
        $histories = $task->statusHistories()->with('changer:id,name,email')->get();
        return ResponseHelper::success($histories, 'Status transition history retrieved');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->taskService->deleteTask($id);
        return ResponseHelper::success(null, 'Task deleted successfully');
    }
}
