<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferralPartner extends Authenticatable
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'wallet_balance'  => 'decimal:2',
        'total_earned'    => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
        'payout_details'  => 'array',
        'status'          => 'integer',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ReferralGroup::class, 'referral_group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attributions(): HasMany
    {
        return $this->hasMany(ReferralAttribution::class, 'referral_partner_id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(ReferralCommission::class, 'referral_partner_id');
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(ReferralWithdrawal::class, 'referral_partner_id');
    }

    /**
     * Determine current active commission rate based on volume tiers.
     */
    public function getCurrentCommissionRate(): array
    {
        $group = $this->group()->with('tiers')->first();
        if (!$group) {
            return [
                'rate'      => 20.00,
                'tier_name' => 'Default (20%)',
                'next_tier' => null,
            ];
        }

        // Count completed sales
        $salesCount = $this->commissions()->where('status', 'approved')->count();

        if ($group->is_tiered && $group->tiers->isNotEmpty()) {
            $currentTier = null;
            $nextTier = null;

            foreach ($group->tiers as $tier) {
                $max = $tier->max_sales ?? PHP_INT_MAX;
                if ($salesCount >= $tier->min_sales && $salesCount <= $max) {
                    $currentTier = $tier;
                } elseif ($salesCount < $tier->min_sales && !$nextTier) {
                    $nextTier = $tier;
                }
            }

            if ($currentTier) {
                return [
                    'rate'         => $currentTier->commission_rate,
                    'tier_name'    => $currentTier->tier_name ?: "Tier ({$currentTier->min_sales}-" . ($currentTier->max_sales ?? '∞') . ")",
                    'sales_count'  => $salesCount,
                    'next_tier'    => $nextTier ? [
                        'name'        => $nextTier->tier_name,
                        'min_sales'   => $nextTier->min_sales,
                        'target_left' => max(0, $nextTier->min_sales - $salesCount),
                        'rate'        => $nextTier->commission_rate,
                    ] : null,
                ];
            }
        }

        return [
            'rate'        => $group->default_commission_rate,
            'tier_name'   => $group->name,
            'sales_count' => $salesCount,
            'next_tier'   => null,
        ];
    }
}
