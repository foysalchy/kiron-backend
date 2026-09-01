<?php

namespace App\Services\Project;

use App\Exceptions\ApiException;
use App\Models\ProjectTimeEntry;
use App\Models\ProjectLaborCost;
use App\Models\ProjectMember;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectTimeService
{
    public function __construct(
        protected ProjectService $projectService
    ) {}

    public function getTimeEntries(array $filters = [], int $perPage = 50)
    {
        $query = ProjectTimeEntry::with([
            'project:id,name,code',
            'phase:id,name',
            'task:id,title',
            'employee:id,first_name,last_name,image',
            'user:id,name',
        ]);

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['task_id'])) {
            $query->where('task_id', $filters['task_id']);
        }

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        return $query->latest('date')->paginate($perPage);
    }

    public function logTime(array $data): ProjectTimeEntry
    {
        return DB::transaction(function () use ($data) {
            $data['user_id'] = $data['user_id'] ?? Auth::id();

            // Calculate duration in hours
            $minutes = (int)($data['duration_minutes'] ?? 0);
            if ($minutes <= 0 && !empty($data['start_time']) && !empty($data['end_time'])) {
                $start = strtotime($data['start_time']);
                $end = strtotime($data['end_time']);
                if ($end > $start) {
                    $minutes = round(($end - $start) / 60);
                }
            }
            $hours = round($minutes / 60, 2);
            $data['duration_minutes'] = $minutes;
            $data['duration_hours'] = $hours;

            // Fetch member's historical cost & billable rate
            if (empty($data['cost_rate']) && !empty($data['employee_id'])) {
                $member = ProjectMember::where('project_id', $data['project_id'])
                    ->where('employee_id', $data['employee_id'])
                    ->first();

                if ($member) {
                    $data['cost_rate'] = $member->cost_rate;
                    $data['billable_rate'] = $member->billable_rate;
                }
            }

            $costRate = (float)($data['cost_rate'] ?? 0);
            $billableRate = (float)($data['billable_rate'] ?? 0);

            $data['total_cost'] = round($hours * $costRate, 2);
            $data['total_billable'] = !empty($data['is_billable']) ? round($hours * $billableRate, 2) : 0;

            $timeEntry = ProjectTimeEntry::create($data);

            // Auto-create matching Labor Cost record if cost rate > 0
            if ($data['total_cost'] > 0 && !empty($data['employee_id'])) {
                $employee = Employee::find($data['employee_id']);
                ProjectLaborCost::create([
                    'project_id' => $timeEntry->project_id,
                    'phase_id' => $timeEntry->phase_id,
                    'task_id' => $timeEntry->task_id,
                    'employee_id' => $timeEntry->employee_id,
                    'worker_name' => $employee ? "{$employee->first_name} {$employee->last_name}" : "Employee #{$timeEntry->employee_id}",
                    'role' => 'Team Member',
                    'cost_type' => 'hourly',
                    'rate' => $costRate,
                    'quantity' => $hours,
                    'unit' => 'Hours',
                    'total_cost' => $data['total_cost'],
                    'date' => $timeEntry->date,
                    'notes' => "Logged via Time Tracking" . (!empty($timeEntry->description) ? ": {$timeEntry->description}" : ""),
                    'time_entry_id' => $timeEntry->id,
                ]);
            }

            $this->projectService->logActivity(
                $timeEntry->project_id,
                $timeEntry->task_id,
                'time_logged',
                "Logged {$hours} hours for Project #{$timeEntry->project_id}"
            );

            return $timeEntry->load(['project', 'task', 'employee']);
        });
    }

    public function deleteTimeEntry(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $timeEntry = ProjectTimeEntry::findOrFail($id);
            $projectId = $timeEntry->project_id;

            // Delete associated labor cost
            ProjectLaborCost::where('time_entry_id', $timeEntry->id)->delete();

            $timeEntry->delete();
            $this->projectService->logActivity($projectId, null, 'time_deleted', "Deleted time entry #{$id}");
            return true;
        });
    }
}
