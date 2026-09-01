<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionCost extends Model
{
    use CompanyScoped;

    protected $fillable = [
        'company_id',
        'production_order_id',
        'raw_material_cost',
        'labor_cost',
        'machine_cost',
        'electricity_cost',
        'overhead_cost',
        'packaging_cost',
        'wastage_cost',
        'other_cost',
        'total_cost',
        'produced_quantity',
        'cost_per_unit',
        'estimated_cost',
        'actual_cost',
        'cost_variance',
    ];

    protected $casts = [
        'raw_material_cost' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'machine_cost' => 'decimal:2',
        'electricity_cost' => 'decimal:2',
        'overhead_cost' => 'decimal:2',
        'packaging_cost' => 'decimal:2',
        'wastage_cost' => 'decimal:2',
        'other_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'produced_quantity' => 'decimal:2',
        'cost_per_unit' => 'decimal:4',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'cost_variance' => 'decimal:2',
    ];

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }
}
