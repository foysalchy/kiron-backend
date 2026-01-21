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
        'batch_number',
        'source_bin_id',
        'destination_bin_id',
        'quantity',
        'serial_numbers',
    ];

    protected $casts = [
        'serial_numbers' => 'array',
    ];

    public function stockMovement() : BelongsTo
    {
        return $this->belongsTo(StockMovement::class);
    }

    public function product() : BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sourceBin() : BelongsTo
    {
        return $this->belongsTo(Bin::class, 'source_bin_id');
    }

    public function destinationBin() : BelongsTo
    {
        return $this->belongsTo(Bin::class, 'destination_bin_id');
    }
}
