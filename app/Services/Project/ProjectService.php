<?php

namespace App\Services\Project;

use App\Exceptions\ApiException;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ProjectMember;
use App\Models\ProjectPhase;
use App\Models\ProjectMilestone;
use App\Models\ProjectTask;
use App\Models\ProjectTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function getProjects(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Project::with([
            'customer:id,name,phone,email',
            'projectManager:id,first_name,last_name,email,image',
            'department:id,name',
            'members.employee:id,first_name,last_name,image',
            'tasks' => function ($q) {
                $q->select('id', 'project_id', 'status', 'is_completed');
            }
        ]);

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            if ($filters['status'] === 'my_projects') {
                $employeeId = Auth::user()?->employee_id;
                $userId = Auth::id();
                $query->where(function ($q) use ($employeeId, $userId) {
                    $q->where('project_manager_id', $employeeId)
                      ->orWhereHas('members', function ($mq) use ($employeeId, $userId) {
                          $mq->where('employee_id', $employeeId)->orWhere('user_id', $userId);
                      });
                });
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if (!empty($filters['priority']) && $filters['priority'] !== 'all') {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['project_type']) && $filters['project_type'] !== 'all') {
            $query->where('project_type', $filters['project_type']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function getProjectById(int $id): Project
    {
        $project = Project::with([
            'customer:id,name,email,phone',
            'projectManager:id,first_name,last_name,email,phone,image',
            'department:id,name,code',
            'createdBy:id,name,email',
            'phases' => function ($q) { $q->orderBy('sort_order'); },
            'milestones',
            'members.employee:id,first_name,last_name,email,phone,image',
            'members.user:id,name,email',
            'tasks' => function ($q) {
                $q->whereNull('parent_task_id')
                  ->with([
                      'assignee:id,first_name,last_name,image',
                      'phase:id,name',
                      'milestone:id,name',
                      'subtasks' => function ($sq) {
                          $sq->with('assignee:id,first_name,last_name,image')->orderBy('sort_order');
                      }
                  ])
                  ->orderBy('sort_order')
                  ->latest('id');
            },
            'timeEntries.employee:id,first_name,last_name',
            'timeEntries.task:id,title',
            'laborCosts.employee:id,first_name,last_name',
            'materials.product:id,title',
            'materials.warehouse:id,name',
            'equipmentCosts',
            'expenses',
            'overheads',
            'budgets',
            'revenues',
            'documents.uploader:id,name',
            'discussions' => function ($q) {
                $q->with('user:id,name,image')->latest();
            },
            'activities' => function ($q) {
                $q->with('user:id,name')->latest()->take(50);
            },
        ])->find($id);

        if (!$project) {
            throw ApiException::notFound('Project');
        }

        return $project;
    }

    public function createProject(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['code'])) {
                $data['code'] = 'PRJ-' . strtoupper(substr(uniqid(), -6));
            }
            $data['created_by'] = Auth::id();

            $members = $data['members'] ?? [];
            $templateId = $data['template_id'] ?? null;
            unset($data['members'], $data['template_id']);

            $project = Project::create($data);

            // Add Project Manager as member if assigned
            if (!empty($project->project_manager_id)) {
                ProjectMember::firstOrCreate([
                    'project_id' => $project->id,
                    'employee_id' => $project->project_manager_id,
                ], [
                    'role' => 'project_manager',
                    'status' => 'active',
                ]);
            }

            // Assign members
            if (!empty($members) && is_array($members)) {
                foreach ($members as $member) {
                    ProjectMember::create([
                        'project_id' => $project->id,
                        'employee_id' => $member['employee_id'] ?? null,
                        'user_id' => $member['user_id'] ?? null,
                        'role' => $member['role'] ?? 'member',
                        'cost_type' => $member['cost_type'] ?? 'hourly',
                        'cost_rate' => $member['cost_rate'] ?? 0,
                        'billable_rate' => $member['billable_rate'] ?? 0,
                        'assigned_date' => now(),
                    ]);
                }
            }

            // Apply template if selected
            if ($templateId) {
                $this->applyTemplate($project->id, $templateId);
            }

            $this->logActivity($project->id, null, 'created', "Created project: {$project->name}");

            return $this->getProjectById($project->id);
        });
    }

    public function updateProject(int $id, array $data): Project
    {
        $project = Project::findOrFail($id);

        return DB::transaction(function () use ($project, $data) {
            $members = $data['members'] ?? null;
            unset($data['members']);

            $project->update($data);

            if ($members !== null && is_array($members)) {
                ProjectMember::where('project_id', $project->id)->delete();
                foreach ($members as $member) {
                    ProjectMember::create([
                        'project_id' => $project->id,
                        'employee_id' => $member['employee_id'] ?? null,
                        'user_id' => $member['user_id'] ?? null,
                        'role' => $member['role'] ?? 'member',
                        'cost_type' => $member['cost_type'] ?? 'hourly',
                        'cost_rate' => $member['cost_rate'] ?? 0,
                        'billable_rate' => $member['billable_rate'] ?? 0,
                        'assigned_date' => now(),
                    ]);
                }
            }

            $this->logActivity($project->id, null, 'updated', "Updated project: {$project->name}");

            return $this->getProjectById($project->id);
        });
    }

    public function deleteProject(int $id): bool
    {
        $project = Project::findOrFail($id);
        $this->logActivity($project->id, null, 'deleted', "Deleted project: {$project->name}");
        return $project->delete();
    }

    public function recalculateProgress(int $projectId): float
    {
        $totalTasks = ProjectTask::where('project_id', $projectId)->count();
        if ($totalTasks === 0) {
            return 0.0;
        }

        $completedTasks = ProjectTask::where('project_id', $projectId)->where('is_completed', true)->count();
        $progress = round(($completedTasks / $totalTasks) * 100, 2);

        Project::where('id', $projectId)->update(['progress' => $progress]);

        return $progress;
    }

    public function applyTemplate(int $projectId, int $templateId): void
    {
        $template = ProjectTemplate::findOrFail($templateId);
        $structure = $template->structure_json;

        if (empty($structure)) return;

        // Create Phases
        if (!empty($structure['phases']) && is_array($structure['phases'])) {
            foreach ($structure['phases'] as $pIndex => $phaseData) {
                $phase = ProjectPhase::create([
                    'project_id' => $projectId,
                    'name' => $phaseData['name'],
                    'sort_order' => $pIndex + 1,
                    'status' => 'pending',
                ]);

                if (!empty($phaseData['tasks']) && is_array($phaseData['tasks'])) {
                    foreach ($phaseData['tasks'] as $tIndex => $taskData) {
                        $task = ProjectTask::create([
                            'project_id' => $projectId,
                            'phase_id' => $phase->id,
                            'title' => $taskData['title'] ?? $taskData['name'],
                            'sort_order' => $tIndex + 1,
                            'status' => 'todo',
                        ]);

                        if (!empty($taskData['subtasks']) && is_array($taskData['subtasks'])) {
                            foreach ($taskData['subtasks'] as $sIndex => $subtaskData) {
                                ProjectTask::create([
                                    'project_id' => $projectId,
                                    'phase_id' => $phase->id,
                                    'parent_task_id' => $task->id,
                                    'title' => is_string($subtaskData) ? $subtaskData : ($subtaskData['title'] ?? $subtaskData['name']),
                                    'sort_order' => $sIndex + 1,
                                    'status' => 'todo',
                                ]);
                            }
                        }
                    }
                }
            }
        }

        // Direct tasks from template
        if (!empty($structure['tasks']) && is_array($structure['tasks']) && empty($structure['phases'])) {
            foreach ($structure['tasks'] as $tIndex => $taskData) {
                $task = ProjectTask::create([
                    'project_id' => $projectId,
                    'title' => is_string($taskData) ? $taskData : ($taskData['title'] ?? $taskData['name']),
                    'sort_order' => $tIndex + 1,
                    'status' => 'todo',
                ]);

                if (!empty($taskData['subtasks']) && is_array($taskData['subtasks'])) {
                    foreach ($taskData['subtasks'] as $sIndex => $subtaskData) {
                        ProjectTask::create([
                            'project_id' => $projectId,
                            'parent_task_id' => $task->id,
                            'title' => is_string($subtaskData) ? $subtaskData : ($subtaskData['title'] ?? $subtaskData['name']),
                            'sort_order' => $sIndex + 1,
                            'status' => 'todo',
                        ]);
                    }
                }
            }
        }
    }

    public function logActivity(int $projectId, ?int $taskId, string $action, string $description, array $properties = []): void
    {
        ProjectActivity::create([
            'project_id' => $projectId,
            'task_id' => $taskId,
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'properties' => $properties,
        ]);
    }
}
