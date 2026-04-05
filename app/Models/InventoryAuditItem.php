<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAuditItem extends Model
{
    protected $fillable = [
        'inventory_audit_id',
        'product_id',
        'variation_id',
        'expected_quantity',
        'actual_quantity',
        'unit_cost',
    ];

    protected $casts = [
        'expected_quantity' => 'integer',
        'actual_quantity' => 'integer',
        'unit_cost' => 'decimal:2',
    ];

    protected $appends = [
        'variance',
        'variance_percentage',
        'variance_type',
        'variance_value',
        'is_counted',
        'product_display_name',
    ];

    /**
     * Relationships
     */
    public function inventoryAudit(): BelongsTo
    {
        return $this->belongsTo(InventoryAudit::class);
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
     * Check if item is counted
     */
    public function getIsCountedAttribute(): bool
    {
        return $this->actual_quantity !== null;
    }

    /**
     * Get variance (actual - expected)
     */
    public function getVarianceAttribute(): int
    {
        if ($this->actual_quantity === null) {
            return 0;
        }
        return $this->actual_quantity - $this->expected_quantity;
    }

    /**
     * Get variance percentage
     */
    public function getVariancePercentageAttribute(): float
    {
        if ($this->actual_quantity === null || $this->expected_quantity == 0) {
            return 0;
        }
        return round(($this->variance / $this->expected_quantity) * 100, 2);
    }

    /**
     * Get variance type
     */
    public function getVarianceTypeAttribute(): string
    {
        if ($this->actual_quantity === null) {
            return 'not_counted';
        }

        $variance = $this->variance;

        if ($variance > 0) {
            return 'overage';
        } elseif ($variance < 0) {
            return 'shortage';
        }

        return 'none';
    }

    /**
     * Get variance value (financial impact)
     */
    public function getVarianceValueAttribute(): float
    {
        if ($this->actual_quantity === null) {
            return 0;
        }
        return round($this->variance * $this->unit_cost, 2);
    }

    /**
     * Check if this is a variation product
     */
    public function isVariation(): bool
    {
        return !empty($this->variation_id);
    }

    /**
     * Get product display name with variation
     */
public function getProductDisplayNameAttribute(): string
    {
        $name = $this->product?->title ?? 'Unknown Product';

        if ($this->variation_id && $this->variation) {
            $attrs = collect($this->variation->attributes)
                ->map(function ($attr) {
                    $group = $attr->attributeGroup?->name
                        ?? $attr->attributeValue?->attributeGroup?->name
                        ?? '';
                    // value অথবা name যেটাই থাকুক null safe করে দিলাম
                    $value = $attr->attributeValue?->value ?? $attr->attributeValue?->name ?? '';
                    
                    return $group && $value ? "{$group}: {$value}" : null;
                })
                ->filter() 
                ->join(', ');

            if ($attrs) {
                $name .= " ({$attrs})";
            }

            if ($this->variation->sku) {
                $name .= " - SKU: {$this->variation->sku}";
            }
        } elseif ($this->product?->sku) {
            $name .= " - SKU: {$this->product->sku}";
        }

        return $name;
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
            'attributes' => collect($this->variation->attributes)->map(function ($attr) {
                return [
                    'group' => $attr->attributeGroup?->name ?? $attr->attributeValue?->attributeGroup?->name ?? '',
                    'value' => $attr->attributeValue?->name ?? '', 
                ];
            }),
        ];
    }
}
