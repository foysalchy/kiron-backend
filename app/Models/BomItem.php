<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BomItem extends Model
{
    use CompanyScoped;

    protected $fillable = [
        'company_id',
        'bill_of_material_id',
        'product_id',
        'product_variation_id',
        'component_type',
        'quantity',
        'unit',
        'unit_cost',
        'wastage_percentage',
        'total_cost',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'wastage_percentage' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function billOfMaterial(): BelongsTo
    {
        return $this->belongsTo(BillOfMaterial::class);
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
