<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralWithdrawal extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'amount'          => 'decimal:2',
        'account_details' => 'array',
        'processed_at'    => 'datetime',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(ReferralPartner::class, 'referral_partner_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
