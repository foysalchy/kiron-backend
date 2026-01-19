<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchasePaymentReturn extends Model
{
     protected $fillable = [
        'purchase_return_id',
        'amount',
        'payment_method',
        'reference_no',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class);
    }
}
