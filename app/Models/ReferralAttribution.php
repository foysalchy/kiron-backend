<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferralAttribution extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'buyer_discount_rate'   => 'decimal:2',
        'buyer_discount_amount' => 'decimal:2',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(ReferralPartner::class, 'referral_partner_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(ReferralCommission::class, 'referral_attribution_id');
    }
}
