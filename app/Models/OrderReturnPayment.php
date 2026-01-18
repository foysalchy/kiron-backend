<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReturnPayment extends Model
{
     protected $fillable = [
        'order_return_id',
        'amount',
        'payment_method',
        'reference_no',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function orderReturn(): BelongsTo
    {
        return $this->belongsTo(OrderReturn::class);
    }
}
