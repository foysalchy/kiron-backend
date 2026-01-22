<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustmentItem extends Model
{
    protected $fillable = [
        'stock_adjustment_id',
        'product_id',
        'bin_id',
        'batch_number',
        'serial_numbers',
        'quantity_to_adjust',
    ];

    protected $casts = [
        'serial_numbers' => 'array',
        'quantity_to_adjust' => 'integer',
    ];

    public function stockAdjustment() : BelongsTo
    {
        return $this->belongsTo(StockAdjustment::class);
    }

    public function product() : BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function bin() : BelongsTo
    {
        return $this->belongsTo(Bin::class);
    }
}
