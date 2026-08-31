<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionOrder extends Model
{
    use SoftDeletes, CompanyScoped;

    protected $fillable = [
        'company_id',
        'order_number',
        'production_plan_id',
        'product_id',
        'product_variation_id',
        'bill_of_material_id',
        'raw_material_warehouse_id',
        'finished_goods_warehouse_id',
        'work_center_id',
        'current_stage_id',
        'planned_quantity',
        'produced_quantity',
        'rejected_quantity',
        'unit',
        'priority',
        'status',
        'allow_partial_production',
        'planned_start_date',
        'expected_completion_date',
        'actual_start_date',
        'actual_completion_date',
        'estimated_total_cost',
        'actual_total_cost',
        'assigned_to',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'planned_quantity' => 'decimal:2',
        'produced_quantity' => 'decimal:2',
        'rejected_quantity' => 'decimal:2',
        'allow_partial_production' => 'boolean',
        'planned_start_date' => 'date',
        'expected_completion_date' => 'date',
        'actual_start_date' => 'datetime',
        'actual_completion_date' => 'datetime',
        'estimated_total_cost' => 'decimal:2',
        'actual_total_cost' => 'decimal:2',
    ];

    public function productionPlan(): BelongsTo
    {
        return $this->belongsTo(ProductionPlan::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class);
    }

    public function billOfMaterial(): BelongsTo
    {
        return $this->belongsTo(BillOfMaterial::class);
    }

    public function rawMaterialWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'raw_material_warehouse_id');
    }

    public function finishedGoodsWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'finished_goods_warehouse_id');
    }

    public function workCenter(): BelongsTo
    {
        return $this->belongsTo(WorkCenter::class);
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(ProductionStage::class, 'current_stage_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductionOrderItem::class);
    }

    public function stageLogs(): HasMany
    {
        return $this->hasMany(ProductionStageLog::class)->orderBy('id', 'asc');
    }

    public function consumptions(): HasMany
    {
        return $this->hasMany(ProductionConsumption::class);
    }

    public function outputs(): HasMany
    {
        return $this->hasMany(ProductionOutput::class);
    }

    public function wastages(): HasMany
    {
        return $this->hasMany(ProductionWastage::class);
    }

    public function qualityChecks(): HasMany
    {
        return $this->hasMany(ProductionQualityCheck::class);
    }

    public function costSummary(): HasOne
    {
        return $this->hasOne(ProductionCost::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
