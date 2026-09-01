<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectLaborCost extends Model
{
    use SoftDeletes, CompanyScoped;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
        'rate' => 'decimal:2',
        'quantity' => 'decimal:2',
        'total_cost' => 'decimal:2',
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

    public function timeEntry(): BelongsTo
    {
        return $this->belongsTo(ProjectTimeEntry::class, 'time_entry_id');
    }
}
