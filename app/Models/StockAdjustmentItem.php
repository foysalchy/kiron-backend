<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustmentItem extends Model
{
    protected $fillable = [
        'stock_adjustment_id',
        'product_id',
        'variation_id',
        'bin_id',
        'batch_number',
        'serial_numbers',
        'quantity_to_adjust',
    ];

    protected $casts = [
        'serial_numbers' => 'array',
        'quantity_to_adjust' => 'integer',
    ];

    public function stockAdjustment(): BelongsTo
    {
        return $this->belongsTo(StockAdjustment::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(Bin::class);
    }
    /**
     * Get the variation
     */
    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
    }

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

            if ($this->variation->sku) {
                $name .= " - SKU: {$this->variation->sku}";
            }
        } elseif ($this->product->sku) {
            $name .= " - SKU: {$this->product->sku}";
        }

        return $name;
    }

    /**
     * Check if this is a variation product
     */
    public function isVariation(): bool
    {
        return !empty($this->variation_id);
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
     * Get adjustment type label
     */
    public function getAdjustmentTypeAttribute(): string
    {
        return $this->quantity_to_adjust > 0 ? 'Increase' : 'Decrease';
    }

    /**
     * Get absolute quantity
     */
    public function getAbsoluteQuantityAttribute(): int
    {
        return abs($this->quantity_to_adjust);
    }
}
