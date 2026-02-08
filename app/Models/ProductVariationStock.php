<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariationStock extends Model
{
    use HasFactory;

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
        return $this->belongsTo(Cell::class, 'bin_id');
    }
}
