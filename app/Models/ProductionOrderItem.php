<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionOrderItem extends Model
{
    use CompanyScoped;

    protected $fillable = [
        'company_id',
        'production_order_id',
        'product_id',
        'product_variation_id',
        'required_quantity',
        'consumed_quantity',
        'wastage_quantity',
        'unit',
        'unit_cost',
        'total_cost',
        'status',
    ];

    protected $casts = [
        'required_quantity' => 'decimal:4',
        'consumed_quantity' => 'decimal:4',
        'wastage_quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:2',
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
}
