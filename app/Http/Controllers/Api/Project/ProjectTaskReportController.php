<?php

namespace App\Http\Controllers\Api\Project;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectTaskReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $groupBy = $request->get('group_by', 'user'); // 'user' or 'project'
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $projectId = $request->get('project_id');
        $employeeId = $request->get('assigned_to');
        $status = $request->get('status');

        $query = ProjectTask::with([
            'project:id,name,code',
            'assignee:id,first_name,last_name,email,image',
        ]);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        if ($employeeId) {
            $query->where('assigned_to', $employeeId);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($fromDate) {
            $query->where(function ($q) use ($fromDate) {
                $q->whereDate('due_date', '>=', $fromDate)
                  ->orWhereDate('created_at', '>=', $fromDate);
            });
        }

        if ($toDate) {
            $query->where(function ($q) use ($toDate) {
                $q->whereDate('due_date', '<=', $toDate)
                  ->orWhereDate('created_at', '<=', $toDate);
            });
        }

        $allTasks = $query->latest('id')->get();

        if ($groupBy === 'user') {
            // Group tasks by assignee
            $grouped = $allTasks->groupBy('assigned_to')->map(function ($tasks, $empId) {
                $assignee = $tasks->first()->assignee;
                $total = $tasks->count();
                $completed = $tasks->where('is_completed', true)->count();
                $inProgress = $tasks->where('status', 'in_progress')->count();
                $todo = $tasks->where('status', 'todo')->count();
                $estHours = (float)$tasks->sum('estimated_hours');
                $actHours = (float)$tasks->sum('actual_hours');
                $rate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

                return [
                    'id' => $empId ?? 0,
                    'name' => $assignee ? $assignee->first_name . ' ' . $assignee->last_name : 'Unassigned',
                    'email' => $assignee?->email,
                    'image' => $assignee?->image,
                    'total_tasks' => $total,
                    'completed_tasks' => $completed,
                    'in_progress_tasks' => $inProgress,
                    'todo_tasks' => $todo,
                    'completion_rate' => $rate,
                    'estimated_hours' => $estHours,
                    'actual_hours' => $actHours,
                    'tasks' => $tasks->values(),
                ];
            })->values();
        } else {
            // Group tasks by project
            $grouped = $allTasks->groupBy('project_id')->map(function ($tasks, $prjId) {
                $project = $tasks->first()->project;
                $total = $tasks->count();
                $completed = $tasks->where('is_completed', true)->count();
                $inProgress = $tasks->where('status', 'in_progress')->count();
                $todo = $tasks->where('status', 'todo')->count();
                $estHours = (float)$tasks->sum('estimated_hours');
                $actHours = (float)$tasks->sum('actual_hours');
                $rate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

                return [
                    'id' => $prjId,
                    'name' => $project?->name ?? 'Unknown Project',
                    'code' => $project?->code,
                    'total_tasks' => $total,
                    'completed_tasks' => $completed,
                    'in_progress_tasks' => $inProgress,
                    'todo_tasks' => $todo,
                    'completion_rate' => $rate,
                    'estimated_hours' => $estHours,
                    'actual_hours' => $actHours,
                    'tasks' => $tasks->values(),
                ];
            })->values();
        }

        // Summary metrics
        $totalCount = $allTasks->count();
        $completedCount = $allTasks->where('is_completed', true)->count();
        $totalEstHours = (float)$allTasks->sum('estimated_hours');
        $totalActHours = (float)$allTasks->sum('actual_hours');

        return ResponseHelper::success([
            'records' => $grouped,
            'summary' => [
                'total_tasks' => $totalCount,
                'completed_tasks' => $completedCount,
                'in_progress_tasks' => $allTasks->where('status', 'in_progress')->count(),
                'completion_rate' => $totalCount > 0 ? round(($completedCount / $totalCount) * 100, 1) : 0,
                'total_estimated_hours' => $totalEstHours,
                'total_actual_hours' => $totalActHours,
            ]
        ], 'Task report generated successfully');
    }
}
