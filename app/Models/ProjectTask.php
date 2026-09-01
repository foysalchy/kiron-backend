<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTask extends Model
{
    use SoftDeletes, CompanyScoped;

    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'current_status_started_at' => 'datetime',
        'is_timer_running' => 'boolean',
        'timer_started_at' => 'datetime',
        'tags' => 'array',
    ];

    public function customStatus(): BelongsTo
    {
        return $this->belongsTo(ProjectTaskStatus::class, 'status_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ProjectTaskStatusHistory::class, 'task_id')->latest();
    }

    public function timerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'timer_user_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(ProjectPhase::class, 'phase_id');
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(ProjectMilestone::class, 'milestone_id');
    }

    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'parent_task_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'parent_task_id')->orderBy('sort_order');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(ProjectTimeEntry::class, 'task_id');
    }

    public function laborCosts(): HasMany
    {
        return $this->hasMany(ProjectLaborCost::class, 'task_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(ProjectMaterial::class, 'task_id');
    }

    public function equipmentCosts(): HasMany
    {
        return $this->hasMany(ProjectEquipmentCost::class, 'task_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(ProjectExpense::class, 'task_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class, 'task_id');
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(ProjectDiscussion::class, 'task_id');
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(ProjectTaskDependency::class, 'task_id');
    }

    public function getSubtaskProgressAttribute(): array
    {
        $total = $this->subtasks()->count();
        $completed = $this->subtasks()->where('is_completed', true)->count();
        $percent = $total > 0 ? round(($completed / $total) * 100) : ($this->is_completed ? 100 : 0);

        return [
            'total' => $total,
            'completed' => $completed,
            'percent' => $percent,
        ];
    }
}
