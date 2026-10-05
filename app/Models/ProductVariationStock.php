<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariationStock extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::saved(function ($stock) {
            if ($stock->variation && $stock->variation->product) {
                \Illuminate\Support\Facades\Cache::forget("product_details_v2_{$stock->variation->product->slug}");
            }
        });

        static::deleted(function ($stock) {
            if ($stock->variation && $stock->variation->product) {
                \Illuminate\Support\Facades\Cache::forget("product_details_v2_{$stock->variation->product->slug}");
            }
        });
    }

    protected $fillable = [
        'product_variation_id',
        'warehouse_id',
        'bin_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Parent variation
     */
    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    /**
     * Warehouse
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Bin/Cell
     */
    public function bin(): BelongsTo
    {
        return $this->belongsTo(Bin::class, 'bin_id');
    }
}
