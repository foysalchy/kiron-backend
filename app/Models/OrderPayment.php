<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPayment extends Model
{
    protected $fillable = [
        'order_id',
        'amount',
        'change_amount',
        'payment_method',
        'reference_no',
        'transaction_id',
        'sender_info',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'sender_info' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    public function getScreenshotUrlAttribute(): ?string
    {
        return $this->screenshot
            ? asset('storage/' . $this->screenshot)
            : null;
    }
}
