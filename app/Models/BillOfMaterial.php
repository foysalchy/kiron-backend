<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillOfMaterial extends Model
{
    use SoftDeletes, CompanyScoped;

    protected $table = 'bills_of_materials';

    protected $fillable = [
        'company_id',
        'bom_number',
        'product_id',
        'product_variation_id',
        'version',
        'production_quantity',
        'unit',
        'labor_cost',
        'machine_cost',
        'electricity_cost',
        'overhead_cost',
        'packaging_cost',
        'other_cost',
        'estimated_material_cost',
        'total_cost',
        'status',
        'effective_from',
        'effective_to',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'production_quantity' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'machine_cost' => 'decimal:2',
        'electricity_cost' => 'decimal:2',
        'overhead_cost' => 'decimal:2',
        'packaging_cost' => 'decimal:2',
        'other_cost' => 'decimal:2',
        'estimated_material_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BomItem::class);
    }

    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Recalculate and update BOM totals
     */
    public function recalculateTotals(): void
    {
        $materialCost = $this->items()->sum('total_cost');
        $overheadSum = (float)$this->labor_cost +
            (float)$this->machine_cost +
            (float)$this->electricity_cost +
            (float)$this->overhead_cost +
            (float)$this->packaging_cost +
            (float)$this->other_cost;

        $this->update([
            'estimated_material_cost' => $materialCost,
            'total_cost' => $materialCost + $overheadSum,
        ]);
    }
}
