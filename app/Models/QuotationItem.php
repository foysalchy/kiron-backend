<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id',
        'product_id',
        'variation_id',
        'quantity',
        'unit_price',
        'discount',
        'tax',
        'subtotal',
        'total',
        'description',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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
                    'group' => $attr->attributeGroup->name ?? $attr->attributeValue->attributeGroup->name ?? '',
                    'value' => $attr->attributeValue->value,
                ];
            }),
        ];
    }

    /**
     * Get product display name with variation
     */
    public function getProductDisplayNameAttribute(): string
    {
        $name = $this->product->title ?? 'Unknown Product';

        if ($this->variation_id && $this->variation) {
            $attrs = $this->variation->attributes
                ->map(function ($attr) {
                    $group = $attr->attributeGroup->name ?? $attr->attributeValue->attributeGroup->name ?? '';
                    $value = $attr->attributeValue->name ?? '';
                    return "{$group}: {$value}";
                })
                ->filter()
                ->join(', ');

            if ($attrs) {
                $name .= " ({$attrs})";
            }
        }

        return $name;
    }
}
