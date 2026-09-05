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
        $group = null;
        if ($this->referral_group_id) {
            $group = ReferralGroup::with(['tiers' => function ($q) {
                $q->orderBy('min_sales', 'asc');
            }])->find($this->referral_group_id);
        }

        if (!$group) {
            $group = ReferralGroup::with(['tiers' => function ($q) {
                $q->orderBy('min_sales', 'asc');
            }])->whereIn('status', [1, '1', 'active'])->first();
        }

        // Count completed sales / converted active referrals
        $salesCount = max(
            $this->commissions()->whereIn('status', ['approved', 'credited'])->count(),
            $this->attributions()->whereHas('company', function ($q) {
                $q->where('status', 1)->orWhere('status', '1')->orWhere('status', 'active');
            })->count()
        );

        $currentTier = null;
        $nextTier = null;
        $allTiers = [];

        $tiersCollection = ($group && $group->tiers && $group->tiers->isNotEmpty()) ? $group->tiers : collect([
            (object)['id' => 1, 'name' => 'Bronze Rank (1-100 sales)', 'min_sales' => 1, 'max_sales' => 100, 'commission_rate' => 20.00],
            (object)['id' => 2, 'name' => 'Silver Rank (101-300 sales)', 'min_sales' => 101, 'max_sales' => 300, 'commission_rate' => 30.00],
            (object)['id' => 3, 'name' => 'Gold Rank (301+ sales)', 'min_sales' => 301, 'max_sales' => null, 'commission_rate' => 35.00],
        ]);

        foreach ($tiersCollection as $tier) {
            $max = $tier->max_sales ?? PHP_INT_MAX;
            $tierName = $tier->name ?? $tier->tier_name ?? "Tier ({$tier->min_sales}-" . ($tier->max_sales ?? '∞') . ")";
            
            $isCurrent = ($salesCount >= ($tier->min_sales <= 1 ? 0 : $tier->min_sales) && $salesCount <= $max);
            $isUnlocked = ($salesCount >= $tier->min_sales);
            $salesNeeded = max(0, $tier->min_sales - $salesCount);

            if ($isCurrent) {
                $currentTier = $tier;
            } elseif ($salesCount < $tier->min_sales && !$nextTier) {
                $nextTier = $tier;
            }

            $allTiers[] = [
                'id'              => $tier->id ?? 0,
                'name'            => $tierName,
                'min_sales'       => $tier->min_sales,
                'max_sales'       => $tier->max_sales,
                'commission_rate' => $tier->commission_rate,
                'is_current'      => $isCurrent,
                'is_unlocked'     => $isUnlocked,
                'sales_needed'    => $salesNeeded,
            ];
        }

        // Default to first tier if not matched
        if (!$currentTier && count($allTiers) > 0) {
            $allTiers[0]['is_current'] = true;
            $currentTier = $tiersCollection->first();
            if (count($allTiers) > 1) {
                $nextTier = $tiersCollection->get(1);
            }
        }

        $currentRate = $currentTier ? $currentTier->commission_rate : ($group?->default_commission_rate ?? 20.00);
        $currentName = $currentTier ? ($currentTier->name ?? $currentTier->tier_name ?? 'Bronze Rank') : 'Bronze Rank';

        return [
            'rate'         => $currentRate,
            'tier_name'    => $currentName,
            'sales_count'  => $salesCount,
            'next_tier'    => $nextTier ? [
                'name'        => $nextTier->name ?? $nextTier->tier_name ?? 'Silver Rank',
                'min_sales'   => $nextTier->min_sales,
                'target_left' => max(0, $nextTier->min_sales - $salesCount),
                'rate'        => $nextTier->commission_rate,
            ] : null,
            'all_tiers'    => $allTiers,
        ];
    }

    public function getReferralLinkAttribute(): string
    {
        return url('/register?ref=' . $this->referral_code);
    }

    public function getTotalSalesCountAttribute(): int
    {
        return $this->commissions()->where('status', 'approved')->count();
    }
}
