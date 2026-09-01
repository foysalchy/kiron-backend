<?php

namespace App\Http\Controllers\Api\Project;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectTask;
use App\Models\ProjectTimeEntry;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectMonitoringController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // 1. Tasks with active running timers
        $activeTimerTasks = ProjectTask::with([
            'project:id,name,code',
            'assignee:id,first_name,last_name,email,image',
            'timerUser:id,name,email,image',
        ])
        ->where('is_timer_running', true)
        ->latest('timer_started_at')
        ->get()
        ->map(function ($task) {
            $startedAt = $task->timer_started_at;
            $elapsedSeconds = $startedAt ? now()->diffInSeconds($startedAt) : 0;
            return [
                'task_id' => $task->id,
                'task_title' => $task->title,
                'project_id' => $task->project_id,
                'project_name' => $task->project?->name,
                'project_code' => $task->project?->code,
                'status' => $task->status,
                'priority' => $task->priority,
                'user_id' => $task->timer_user_id,
                'user_name' => $task->timerUser?->name ?? ($task->assignee ? $task->assignee->first_name . ' ' . $task->assignee->last_name : 'Team Member'),
                'user_image' => $task->timerUser?->image ?? $task->assignee?->image,
                'timer_started_at' => $startedAt?->toISOString(),
                'elapsed_seconds' => $elapsedSeconds,
                'is_active' => true,
            ];
        });

        // 2. Recent In-Progress Tasks (active today without timer)
        $inProgressTasks = ProjectTask::with([
            'project:id,name,code',
            'assignee:id,first_name,last_name,email,image',
        ])
        ->where('is_timer_running', false)
        ->where('status', 'in_progress')
        ->where('is_completed', false)
        ->latest('updated_at')
        ->take(15)
        ->get()
        ->map(function ($task) {
            $startedAt = $task->current_status_started_at ?? $task->updated_at;
            $elapsedSeconds = $startedAt ? now()->diffInSeconds($startedAt) : 0;
            return [
                'task_id' => $task->id,
                'task_title' => $task->title,
                'project_id' => $task->project_id,
                'project_name' => $task->project?->name,
                'project_code' => $task->project?->code,
                'status' => $task->status,
                'priority' => $task->priority,
                'user_name' => $task->assignee ? $task->assignee->first_name . ' ' . $task->assignee->last_name : 'Unassigned',
                'user_image' => $task->assignee?->image,
                'timer_started_at' => $startedAt?->toISOString(),
                'elapsed_seconds' => $elapsedSeconds,
                'is_active' => false,
            ];
        });

        // 3. KPI Summary
        $activeTimerCount = $activeTimerTasks->count();
        $inProgressCount = ProjectTask::where('status', 'in_progress')->where('is_completed', false)->count();
        $completedTodayCount = ProjectTask::where('is_completed', true)->whereDate('completed_at', now()->format('Y-m-d'))->count();
        $totalHoursToday = (float)ProjectTimeEntry::whereDate('date', now()->format('Y-m-d'))->sum('duration_hours');

        return ResponseHelper::success([
            'active_timers' => $activeTimerTasks,
            'in_progress_tasks' => $inProgressTasks,
            'summary' => [
                'active_timers_count' => $activeTimerCount,
                'in_progress_count' => $inProgressCount,
                'completed_today_count' => $completedTodayCount,
                'total_hours_today' => $totalHoursToday,
            ],
        ], 'Live task monitoring data retrieved');
    }
}
