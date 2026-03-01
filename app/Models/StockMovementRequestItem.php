<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovementRequestItem extends Model
{
    protected $fillable = [
        'stock_movement_request_id',
        'product_id',
        'variation_id',
        'transfer_quantity',
    ];

    protected $casts = [
        'transfer_quantity' => 'integer',
    ];

    public function stockMovementRequest(): BelongsTo
    {
        return $this->belongsTo(StockMovementRequest::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the variation
     */
    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
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

    /**
     * Get available stock for this item
     */
    public function getAvailableStockAttribute(): int
    {
        if ($this->isVariation()) {
            // Get variation stock from product_variation_stocks
            $stock = $this->variation->stocks()
                ->where('warehouse_id', $this->stockMovementRequest->source_warehouse_id)
                ->first();

            return $stock ? $stock->quantity : 0;
        } else {
            // Get single product stock from warehouse_info
            $warehouseInfo = collect($this->product->warehouse_info ?? []);
            $stock = $warehouseInfo->firstWhere(
                'warehouse_id',
                (string) $this->stockMovementRequest->source_warehouse_id
            );

            return $stock['quantity'] ?? 0;
        }
    }
}
