<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'image',
        'regular_price',
        'purchase_price',
        'discount_type',
        'discount',
        'stock_quantity',
        'available_stock',
        'stock_status',
        'combination_hash',
    ];

    protected $casts = [
        'regular_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'stock_quantity' => 'integer',
        'available_stock' => 'integer',
        'product_id' => 'integer',

    ];

    /**
     * Parent product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Variation attributes
     */
    public function attributes(): HasMany
    {
        return $this->hasMany(ProductVariationAttribute::class);
    }

    /**
     * Warehouse stocks
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(ProductVariationStock::class);
    }
    public function galleries(): HasMany
    {
        return $this->hasMany(VariationGallery::class, 'variation_id');
    }
    public function barcode()
    {
        return $this->morphOne(BarCode::class, 'barcodeable');
    }
    /**
     * Calculate final price after discount
     */
    public function getFinalPriceAttribute(): float
    {
        if ($this->discount <= 0) {
            return $this->regular_price;
        }

        if ($this->discount_type === 'percent') {
            return $this->regular_price - ($this->regular_price * ($this->discount / 100));
        }

        return $this->regular_price - $this->discount;
    }

    /**
     * Get variation display name
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->attributes()
            ->with(['attributeValue'])
            ->get()
            ->pluck('attributeValue.name')
            ->join(' / ');
    }
    public function getAvailableStockAttribute(): int
    {
        if ($this->relationLoaded('stocks')) {
            return $this->stocks->sum('quantity');
        }
        return $this->stocks()->sum('quantity');
    }

    public function getSalePriceAttribute()
    {
        // regular_price থেকে discount বিয়োগ করা হচ্ছে
        $price = $this->regular_price - $this->discount;
        return $price > 0 ? $price : $this->regular_price;
    }
}
