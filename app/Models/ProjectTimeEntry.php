<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTimeEntry extends Model
{
    use SoftDeletes, CompanyScoped;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
        'is_billable' => 'boolean',
        'cost_rate' => 'decimal:2',
        'billable_rate' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'total_billable' => 'decimal:2',
        'duration_hours' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(ProjectPhase::class, 'phase_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
