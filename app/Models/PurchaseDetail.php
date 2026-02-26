<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseDetail extends Model
{

    protected $fillable = [
        'purchase_id',
        'product_id',
        'variation_id',
        'quantity',
        'purchase_price',
        'unit_cost',
        'discount',
        'tax_group_id',
        'tax',
        'total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'purchase_price' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relationships
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->select('id', 'title', 'thumbnail');
    }
    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
    }

    /**
     * Get variation info with attributes
     */
    public function getVariationInfoAttribute()
    {
        if (!$this->variation_id || !$this->variation) {
            return null;
        }

        return [
            'id' => $this->variation->id,
            'sku' => $this->variation->sku,
            'attributes' => $this->variation->attributes->map(function ($attr) {
                return [
                    'group' => $attr->attributeGroup->name,
                    'value' => $attr->attributeValue->name,
                ];
            }),
        ];
    }
}
