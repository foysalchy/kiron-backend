<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Requisition;

class RequisitionDetail extends Model
{
    protected $fillable = [
        'requisition_id',
        'product_id',
        'variation_id',
        'unit',
        'quantity',
        'price',
        'amount',
    ];

    protected $casts = [

        'quantity' => 'integer',
        'price' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function requisition(): BelongsTo

    {
        return $this->belongsTo(Requisition::class);
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
