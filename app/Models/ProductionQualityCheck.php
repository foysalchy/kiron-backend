<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionQualityCheck extends Model
{
    use CompanyScoped;

    protected $fillable = [
        'company_id',
        'production_order_id',
        'product_id',
        'product_variation_id',
        'inspected_quantity',
        'passed_quantity',
        'failed_quantity',
        'defective_quantity',
        'inspector_id',
        'inspection_date',
        'status',
        'defect_details',
        'defective_warehouse_id',
        'notes',
    ];

    protected $casts = [
        'inspected_quantity' => 'decimal:2',
        'passed_quantity' => 'decimal:2',
        'failed_quantity' => 'decimal:2',
        'defective_quantity' => 'decimal:2',
        'inspection_date' => 'datetime',
        'defect_details' => 'array',
    ];

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function defectiveWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'defective_warehouse_id');
    }
}
