<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferralGroup extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'default_commission_rate' => 'decimal:2',
        'buyer_discount_rate'     => 'decimal:2',
        'is_tiered'               => 'boolean',
        'is_recurring'            => 'boolean',
        'recurring_rates'         => 'array',
        'status'                  => 'integer',
    ];

    public function tiers(): HasMany
    {
        return $this->hasMany(ReferralGroupTier::class, 'referral_group_id')->orderBy('min_sales', 'asc');
    }

    public function partners(): HasMany
    {
        return $this->hasMany(ReferralPartner::class, 'referral_group_id');
    }
}
