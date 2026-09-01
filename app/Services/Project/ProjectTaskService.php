<?php

namespace App\Services\Project;

use App\Exceptions\ApiException;
use App\Models\ProjectTask;
use App\Models\ProjectTaskDependency;
use App\Models\ProjectTaskStatusHistory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectTaskService
{
    public function __construct(
        protected ProjectService $projectService
    ) {}

    public function getTasks(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        $query = ProjectTask::with([
            'project:id,name,code,status,priority',
            'phase:id,name',
            'milestone:id,name',
            'assignee:id,first_name,last_name,image',
            'subtasks' => function ($q) {
                $q->with('assignee:id,first_name,last_name,image')->orderBy('sort_order');
            }
        ])->whereNull('parent_task_id');

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['phase_id'])) {
            $query->where('phase_id', $filters['phase_id']);
        }

        if (!empty($filters['milestone_id'])) {
            $query->where('milestone_id', $filters['milestone_id']);
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority']) && $filters['priority'] !== 'all') {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (!empty($filters['from_date'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereDate('due_date', '>=', $filters['from_date'])
                  ->orWhereDate('start_date', '>=', $filters['from_date'])
                  ->orWhereDate('created_at', '>=', $filters['from_date']);
            });
        }

        if (!empty($filters['to_date'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereDate('due_date', '<=', $filters['to_date'])
                  ->orWhereDate('start_date', '<=', $filters['to_date'])
                  ->orWhereDate('created_at', '<=', $filters['to_date']);
            });
        }

        if (!empty($filters['quick_filter'])) {
            $employeeId = Auth::user()?->employee_id;
            switch ($filters['quick_filter']) {
                case 'my_tasks':
                    $query->where('assigned_to', $employeeId);
                    break;
                case 'due_today':
                    $query->whereDate('due_date', now()->format('Y-m-d'));
                    break;
                case 'due_week':
                    $query->whereBetween('due_date', [now()->startOfWeek()->format('Y-m-d'), now()->endOfWeek()->format('Y-m-d')]);
                    break;
                case 'overdue':
                    $query->where('is_completed', false)->where('due_date', '<', now()->format('Y-m-d'));
                    break;
                case 'high_priority':
                    $query->whereIn('priority', ['high', 'urgent']);
                    break;
                case 'unassigned':
                    $query->whereNull('assigned_to');
                    break;
            }
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('sort_order')->latest('id')->paginate($perPage);
    }

    public function getTaskById(int $id): ProjectTask
    {
        $task = ProjectTask::with([
            'project',
            'phase',
            'milestone',
            'assignee',
            'subtasks.assignee',
            'timeEntries.employee',
            'laborCosts.employee',
            'materials.product',
            'equipmentCosts',
            'expenses',
            'documents.uploader',
            'discussions.user',
            'dependencies.dependsOnTask',
        ])->find($id);

        if (!$task) {
            throw ApiException::notFound('Task');
        }

        return $task;
    }

    public function createTask(array $data): ProjectTask
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = Auth::id();

            // Set default sort order
            $maxSort = ProjectTask::where('project_id', $data['project_id'])
                ->where('parent_task_id', $data['parent_task_id'] ?? null)
                ->max('sort_order') ?? 0;
            $data['sort_order'] = $maxSort + 1;

            if (!empty($data['status']) && $data['status'] === 'completed') {
                $data['is_completed'] = true;
                $data['completed_at'] = now();
            }

            $task = ProjectTask::create($data);

            $this->projectService->recalculateProgress($task->project_id);
            $this->projectService->logActivity($task->project_id, $task->id, 'task_created', "Created task: {$task->title}");

            return $this->getTaskById($task->id);
        });
    }

    public function createBulkTasks(int $projectId, string $taskTitlesText, ?int $phaseId = null, ?int $parentTaskId = null): array
    {
        $lines = array_filter(array_map('trim', explode("\n", $taskTitlesText)));
        $createdTasks = [];

        DB::transaction(function () use ($projectId, $lines, $phaseId, $parentTaskId, &$createdTasks) {
            $maxSort = ProjectTask::where('project_id', $projectId)
                ->where('parent_task_id', $parentTaskId)
                ->max('sort_order') ?? 0;

            foreach ($lines as $index => $title) {
                if (empty($title)) continue;

                $task = ProjectTask::create([
                    'project_id' => $projectId,
                    'phase_id' => $phaseId,
                    'parent_task_id' => $parentTaskId,
                    'title' => $title,
                    'status' => 'todo',
                    'sort_order' => $maxSort + $index + 1,
                    'created_by' => Auth::id(),
                ]);
                $createdTasks[] = $task;
            }

            $this->projectService->recalculateProgress($projectId);
            $this->projectService->logActivity($projectId, null, 'bulk_tasks_created', "Created " . count($createdTasks) . " tasks");
        });

        return $createdTasks;
    }

    public function updateTask(int $id, array $data): ProjectTask
    {
        $task = ProjectTask::findOrFail($id);

        return DB::transaction(function () use ($task, $data) {
            $oldStatus = $task->status;

            // Check status change
            if (isset($data['status'])) {
                if ($data['status'] === 'completed' && !$task->is_completed) {
                    $data['is_completed'] = true;
                    $data['completed_at'] = now();
                } elseif ($data['status'] !== 'completed' && $task->is_completed) {
                    $data['is_completed'] = false;
                    $data['completed_at'] = null;
                }
            }

            if (isset($data['is_completed'])) {
                $data['status'] = $data['is_completed'] ? 'completed' : 'todo';
                $data['completed_at'] = $data['is_completed'] ? now() : null;
            }

            // Track status duration history if status changed
            if (isset($data['status']) && $data['status'] !== $oldStatus) {
                $startedAt = $task->current_status_started_at ?? $task->created_at;
                $endedAt = now();
                $duration = $startedAt ? $endedAt->diffInSeconds($startedAt) : 0;

                ProjectTaskStatusHistory::create([
                    'company_id' => $task->company_id,
                    'task_id' => $task->id,
                    'from_status' => $oldStatus,
                    'to_status' => $data['status'],
                    'changed_by' => Auth::id(),
                    'duration_seconds' => max(0, $duration),
                    'started_at' => $startedAt,
                    'ended_at' => $endedAt,
                ]);

                $data['current_status_started_at'] = now();
            }

            $task->update($data);

            // Cascade completion to subtasks if requested
            if (!empty($data['complete_subtasks']) && $task->is_completed) {
                ProjectTask::where('parent_task_id', $task->id)->update([
                    'status' => 'completed',
                    'is_completed' => true,
                    'completed_at' => now(),
                ]);
            }

            $this->projectService->recalculateProgress($task->project_id);
            $this->projectService->logActivity($task->project_id, $task->id, 'task_updated', "Updated task: {$task->title}");

            return $this->getTaskById($task->id);
        });
    }

    public function toggleComplete(int $id): ProjectTask
    {
        $task = ProjectTask::findOrFail($id);
        $newComplete = !$task->is_completed;
        $oldStatus = $task->status;
        $newStatus = $newComplete ? 'completed' : 'todo';

        $startedAt = $task->current_status_started_at ?? $task->created_at;
        $endedAt = now();
        $duration = $startedAt ? $endedAt->diffInSeconds($startedAt) : 0;

        ProjectTaskStatusHistory::create([
            'company_id' => $task->company_id,
            'task_id' => $task->id,
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'changed_by' => Auth::id(),
            'duration_seconds' => max(0, $duration),
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
        ]);

        $task->update([
            'is_completed' => $newComplete,
            'status' => $newStatus,
            'completed_at' => $newComplete ? now() : null,
            'current_status_started_at' => now(),
        ]);

        $this->projectService->recalculateProgress($task->project_id);
        $this->projectService->logActivity($task->project_id, $task->id, $newComplete ? 'task_completed' : 'task_reopened', "Marked task '{$task->title}' as " . ($newComplete ? 'completed' : 'incomplete'));

        return $this->getTaskById($task->id);
    }

    public function startTimer(int $id): ProjectTask
    {
        $task = ProjectTask::findOrFail($id);
        $userId = Auth::id();

        // Stop any other active timer by this user
        ProjectTask::where('timer_user_id', $userId)
            ->where('is_timer_running', true)
            ->where('id', '!=', $id)
            ->update([
                'is_timer_running' => false,
                'timer_started_at' => null,
                'timer_user_id' => null,
            ]);

        $task->update([
            'is_timer_running' => true,
            'timer_started_at' => now(),
            'timer_user_id' => $userId,
            'status' => $task->status === 'todo' ? 'in_progress' : $task->status,
        ]);

        return $this->getTaskById($id);
    }

    public function stopTimer(int $id): ProjectTask
    {
        $task = ProjectTask::findOrFail($id);
        if ($task->is_timer_running && $task->timer_started_at) {
            $seconds = now()->diffInSeconds($task->timer_started_at);
            $hours = round($seconds / 3600, 2);

            if ($hours > 0) {
                // Log time entry
                $task->timeEntries()->create([
                    'company_id' => $task->company_id,
                    'project_id' => $task->project_id,
                    'employee_id' => Auth::user()?->employee_id,
                    'user_id' => Auth::id(),
                    'date' => now()->format('Y-m-d'),
                    'duration_hours' => max(0.1, $hours),
                    'description' => 'Timer log for task: ' . $task->title,
                ]);

                $task->increment('actual_hours', $hours);
            }
        }

        $task->update([
            'is_timer_running' => false,
            'timer_started_at' => null,
            'timer_user_id' => null,
        ]);

        return $this->getTaskById($id);
    }

    public function reorderTasks(array $taskOrders): bool
    {
        DB::transaction(function () use ($taskOrders) {
            foreach ($taskOrders as $orderItem) {
                if (isset($orderItem['id']) && isset($orderItem['sort_order'])) {
                    ProjectTask::where('id', $orderItem['id'])->update([
                        'sort_order' => $orderItem['sort_order'],
                        'status' => $orderItem['status'] ?? DB::raw('status'),
                        'phase_id' => $orderItem['phase_id'] ?? DB::raw('phase_id'),
                        'parent_task_id' => $orderItem['parent_task_id'] ?? DB::raw('parent_task_id'),
                    ]);
                }
            }
        });

        return true;
    }

    public function deleteTask(int $id): bool
    {
        $task = ProjectTask::findOrFail($id);
        $projectId = $task->project_id;

        // Delete subtasks
        ProjectTask::where('parent_task_id', $task->id)->delete();

        $task->delete();

        $this->projectService->recalculateProgress($projectId);
        $this->projectService->logActivity($projectId, null, 'task_deleted', "Deleted task: {$task->title}");

        return true;
    }
}
