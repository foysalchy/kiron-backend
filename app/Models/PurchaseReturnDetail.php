<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReturnDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_return_id',
        'product_id',
        'variation_id',
        'quantity',
        'unit_price',
        'discount',
        'tax_group_id',
        'tax',
        'total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'total' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class);
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
