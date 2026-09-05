<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralGroupTier extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'min_sales'       => 'integer',
        'max_sales'       => 'integer',
        'commission_rate' => 'decimal:2',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ReferralGroup::class, 'referral_group_id');
    }
}
