<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectPhase extends Model
{
    use SoftDeletes, CompanyScoped;

    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'progress' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'phase_id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class, 'phase_id');
    }

    public function laborCosts(): HasMany
    {
        return $this->hasMany(ProjectLaborCost::class, 'phase_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(ProjectMaterial::class, 'phase_id');
    }

    public function equipmentCosts(): HasMany
    {
        return $this->hasMany(ProjectEquipmentCost::class, 'phase_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(ProjectExpense::class, 'phase_id');
    }

    public function getTotalCostAttribute(): float
    {
        return (float)$this->laborCosts()->sum('total_cost')
            + (float)$this->materials()->sum('total_cost')
            + (float)$this->equipmentCosts()->sum('total_cost')
            + (float)$this->expenses()->sum('amount');
    }
}
