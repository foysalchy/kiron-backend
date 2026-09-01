<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes, CompanyScoped;

    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'deadline' => 'date',
        'contract_value' => 'decimal:2',
        'budget' => 'decimal:2',
        'progress' => 'decimal:2',
        'tags' => 'array',
    ];

    // --- Relationships ---

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'customer_id');
    }

    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'project_manager_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function phases(): HasMany
    {
        return $this->hasMany(ProjectPhase::class)->orderBy('sort_order');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class)->orderBy('sort_order');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(ProjectTimeEntry::class);
    }

    public function laborCosts(): HasMany
    {
        return $this->hasMany(ProjectLaborCost::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(ProjectMaterial::class);
    }

    public function equipmentCosts(): HasMany
    {
        return $this->hasMany(ProjectEquipmentCost::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(ProjectExpense::class);
    }

    public function overheads(): HasMany
    {
        return $this->hasMany(ProjectOverhead::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(ProjectBudget::class);
    }

    public function revenues(): HasMany
    {
        return $this->hasMany(ProjectRevenue::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class);
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(ProjectDiscussion::class)->whereNull('parent_id')->with('replies');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ProjectActivity::class)->latest();
    }

    // --- Calculated Attributes ---

    public function getTotalLaborCostAttribute(): float
    {
        return (float) $this->laborCosts()->sum('total_cost');
    }

    public function getTotalMaterialCostAttribute(): float
    {
        return (float) $this->materials()->sum('total_cost');
    }

    public function getTotalEquipmentCostAttribute(): float
    {
        return (float) $this->equipmentCosts()->sum('total_cost');
    }

    public function getTotalExpenseCostAttribute(): float
    {
        return (float) $this->expenses()->sum('amount');
    }

    public function getTotalOverheadCostAttribute(): float
    {
        return (float) $this->overheads()->sum('total_cost');
    }

    public function getTotalCostAttribute(): float
    {
        return $this->total_labor_cost
            + $this->total_material_cost
            + $this->total_equipment_cost
            + $this->total_expense_cost
            + $this->total_overhead_cost;
    }

    public function getTotalRevenueAttribute(): float
    {
        return (float) $this->revenues()->sum('amount');
    }

    public function getTotalReceivedRevenueAttribute(): float
    {
        return (float) $this->revenues()->sum('received_amount');
    }

    public function getGrossProfitAttribute(): float
    {
        return $this->total_revenue - $this->total_cost;
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->total_revenue <= 0) {
            return 0;
        }
        return round(($this->gross_profit / $this->total_revenue) * 100, 2);
    }
}
