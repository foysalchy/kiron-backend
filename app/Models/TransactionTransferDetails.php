<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionTransferDetails extends Model
{
    protected $fillable = [
        'transfer_id',
        'chart_of_account_id',
        'amount',
        ];

    public function transferTo(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class,'chart_of_account_id');
    }
    public function transfer(): BelongsTo
    {
        return $this->belongsTo(TransactionTransfer::class, 'transfer_id');
    }
}
