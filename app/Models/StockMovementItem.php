<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class StockMovementItem extends Model
{
    protected $fillable = [
        'stock_movement_id',
        'product_id',
        'variation_id',
        'batch_number',
        'source_bin_id',
        'destination_bin_id',
        'quantity',
        'serial_numbers',
    ];

    protected $casts = [
        'serial_numbers' => 'array',
    ];

    public function stockMovement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
    }


    public function sourceBin(): BelongsTo
    {
        return $this->belongsTo(Bin::class, 'source_bin_id');
    }

    public function destinationBin(): BelongsTo
    {
        return $this->belongsTo(Bin::class, 'destination_bin_id');
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
                    'value' => $attr->attributeValue->name ?? '',
                ];
            }),
        ];
    }
}
