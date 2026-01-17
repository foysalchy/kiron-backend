<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosOrderPayment extends Model
{
    protected $fillable = [
        'pos_order_id',
        'amount',
        'payment_method',
        'reference_no',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function posOrder(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class);
    }
}
