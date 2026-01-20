<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovementRequestItem extends Model
{
    protected $fillable = [
        'stock_movement_request_id',
        'product_id',
        'transfer_quantity',
    ];

    protected $casts = [
        'transfer_quantity' => 'integer',
    ];

    public function stockMovementRequest() : BelongsTo
    {
        return $this->belongsTo(StockMovementRequest::class);
    }

    public function product() : BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}