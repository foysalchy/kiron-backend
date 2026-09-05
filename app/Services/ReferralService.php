<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\ReferralAttribution;
use App\Models\ReferralCommission;
use App\Models\ReferralGroup;
use App\Models\ReferralPartner;
use App\Models\ReferralWithdrawal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReferralService
{
    /**
     * Validate a referral code and return partner & buyer discount rate.
     */
    public function validateReferralCode(?string $code): ?array
    {
        if (empty($code)) {
            return null;
        }

        $code = trim(strtoupper($code));
        $partner = ReferralPartner::with('group')
            ->where('referral_code', $code)
            ->whereIn('status', [1, '1', 'active'])
            ->first();

        if (!$partner) {
            return null;
        }

        $buyerDiscount = floatval($partner->group?->buyer_discount_rate ?? 0);

        return [
            'valid'               => true,
            'partner_id'          => $partner->id,
            'partner_name'        => $partner->name,
            'referral_code'       => $partner->referral_code,
            'buyer_discount_rate' => $buyerDiscount,
        ];
    }

    /**
     * Record attribution when a company signs up with a referral code.
     */
    public function recordAttribution(string $code, int $companyId): ?ReferralAttribution
    {
        $validation = $this->validateReferralCode($code);
        if (!$validation) {
            return null;
        }

        return ReferralAttribution::firstOrCreate(
            ['company_id' => $companyId],
            [
                'referral_partner_id'   => $validation['partner_id'],
                'referral_code_used'    => $validation['referral_code'],
                'buyer_discount_rate'   => $validation['buyer_discount_rate'],
                'status'                => 'registered',
            ]
        );
    }

    /**
     * Process commission for a successful subscription payment.
     */
    public function processSubscriptionCommission(CompanySubscription $subscription, ?float $amountPaid = null): ?ReferralCommission
    {
        DB::beginTransaction();
        try {
            $companyId = $subscription->company_id;
            $attribution = ReferralAttribution::where('company_id', $companyId)->first();

            if (!$attribution) {
                DB::commit();
                return null;
            }

            $partner = ReferralPartner::with('group.tiers')->find($attribution->referral_partner_id);
            if (!$partner || !in_array($partner->status, [1, '1', 'active'])) {
                DB::commit();
                return null;
            }

            $saleAmount = floatval($amountPaid ?? $subscription->amount_paid ?? 0);
            if ($saleAmount <= 0) {
                DB::commit();
                return null;
            }

            // Determine rate from current partner's tier rank
            $tierInfo = $partner->getCurrentCommissionRate();
            $commissionRate = floatval($tierInfo['rate'] ?? 20.00);
            $commissionAmount = round(($saleAmount * $commissionRate) / 100, 2);

            $commission = ReferralCommission::create([
                'referral_partner_id'     => $partner->id,
                'referral_attribution_id' => $attribution->id,
                'company_id'              => $companyId,
                'company_subscription_id' => $subscription->id,
                'sale_amount'             => $saleAmount,
                'commission_rate'         => $commissionRate,
                'commission_amount'       => $commissionAmount,
                'tier_applied'            => $tierInfo['tier_name'] ?? 'Default',
                'status'                  => 'approved',
                'notes'                   => "Commission on subscription ID #{$subscription->id}",
            ]);

            // Update partner wallet balance and total earned
            $partner->increment('wallet_balance', $commissionAmount);
            $partner->increment('total_earned', $commissionAmount);

            // Update attribution status to active
            $attribution->update([
                'status' => 'subscribed_active',
                'first_subscribed_at' => now(),
            ]);

            DB::commit();
            Log::info("Referral commission #{$commission->id} created: {$commissionAmount} for partner #{$partner->id}");
            return $commission;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing referral commission: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Register a new partner from the Partner Portal.
     */
    public function registerPartner(array $data): ReferralPartner
    {
        $groupId = $data['referral_group_id'] ?? null;
        if (!$groupId) {
            $defaultGroup = ReferralGroup::whereIn('status', [1, '1', 'active'])->first();
            $groupId = $defaultGroup?->id;
        }

        // Generate unique code (e.g. DORJA-XYZ123)
        $code = 'REF-' . strtoupper(Str::random(6));
        while (ReferralPartner::where('referral_code', $code)->exists()) {
            $code = 'REF-' . strtoupper(Str::random(6));
        }

        return ReferralPartner::create([
            'referral_group_id' => $groupId,
            'name'              => $data['name'],
            'email'             => $data['email'],
            'phone'             => $data['phone'] ?? null,
            'password'          => Hash::make($data['password']),
            'referral_code'     => $code,
            'payout_method'     => $data['payout_method'] ?? 'bkash',
            'payout_details'    => $data['payout_details'] ?? null,
            'status'            => 'active',
        ]);
    }

    /**
     * Partner submits a withdrawal request.
     */
    public function requestWithdrawal($partner, float $amount, string $paymentMethod, string $accountDetails, ?string $note = null): ReferralWithdrawal
    {
        if (!$partner instanceof ReferralPartner) {
            $partner = ReferralPartner::findOrFail($partner);
        }

        if ($amount < 500) {
            throw new \Exception('Minimum withdrawal amount is ৳ 500.00');
        }

        if ($partner->wallet_balance < $amount) {
            throw new \Exception('Insufficient wallet balance. Available: ৳ ' . number_format($partner->wallet_balance, 2));
        }

        DB::beginTransaction();
        try {
            $requestNo = 'WTH-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            $withdrawal = ReferralWithdrawal::create([
                'referral_partner_id' => $partner->id,
                'request_no'          => $requestNo,
                'amount'              => $amount,
                'payment_method'      => $paymentMethod,
                'account_details'     => $accountDetails,
                'status'              => 'pending',
                'notes'               => $note,
            ]);

            // Deduct from wallet balance
            $partner->decrement('wallet_balance', $amount);

            DB::commit();
            return $withdrawal;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Super Admin approves a withdrawal request.
     */
    public function approveWithdrawal(int $withdrawalId, ?string $transactionReference = null, ?string $adminNotes = null): ReferralWithdrawal
    {
        DB::beginTransaction();
        try {
            $withdrawal = ReferralWithdrawal::with('partner')->findOrFail($withdrawalId);
            if ($withdrawal->status !== 'pending') {
                throw new \Exception('This withdrawal request is already ' . $withdrawal->status);
            }

            $withdrawal->update([
                'status'                => 'approved',
                'processed_at'          => Carbon::now(),
                'transaction_reference' => $transactionReference,
                'admin_notes'           => $adminNotes,
            ]);

            // Increment partner total withdrawn
            $withdrawal->partner->increment('total_withdrawn', $withdrawal->amount);

            DB::commit();
            return $withdrawal;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Super Admin rejects a withdrawal request.
     */
    public function rejectWithdrawal(int $withdrawalId, ?string $adminNotes = null): ReferralWithdrawal
    {
        DB::beginTransaction();
        try {
            $withdrawal = ReferralWithdrawal::with('partner')->findOrFail($withdrawalId);
            if ($withdrawal->status !== 'pending') {
                throw new \Exception('This withdrawal request is already ' . $withdrawal->status);
            }

            $withdrawal->update([
                'status'       => 'rejected',
                'processed_at' => Carbon::now(),
                'admin_notes'  => $adminNotes ?? 'Rejected by admin',
            ]);

            // Refund back to partner's wallet balance
            $withdrawal->partner->increment('wallet_balance', $withdrawal->amount);

            DB::commit();
            return $withdrawal;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get aggregated dashboard stats for partner portal.
     */
    public function getPartnerDashboardStats($partner): array
    {
        if (!$partner instanceof ReferralPartner) {
            $partner = ReferralPartner::with('group.tiers')->findOrFail($partner);
        }

        $totalAttributions = $partner->attributions()->count();

        // Count active vs inactive referred companies
        $activeCount = $partner->attributions()
            ->whereHas('company', function ($q) {
                $q->where('status', 1)->orWhere('status', '1')->orWhere('status', 'active');
            })->count();

        $inactiveCount = max(0, $totalAttributions - $activeCount);

        $tierInfo = $partner->getCurrentCommissionRate();
        $currentRate = $tierInfo['rate'] ?? ($partner->group->default_commission_rate ?? 20.00);

        return [
            'total_referrals'         => $totalAttributions,
            'active_referrals'        => $activeCount,
            'inactive_referrals'      => $inactiveCount,
            'wallet_balance'          => $partner->wallet_balance,
            'total_earned'            => $partner->total_earned,
            'total_withdrawn'         => $partner->total_withdrawn,
            'current_commission_rate' => $currentRate,
            'tier_info'               => [
                'current_tier_name' => $tierInfo['tier_name'] ?? 'Standard Tier',
                'sales_count'       => $tierInfo['sales_count'] ?? 0,
                'next_tier'         => $tierInfo['next_tier'] ?? null,
                'sales_needed'      => $tierInfo['next_tier']['target_left'] ?? 0,
                'all_tiers'         => $tierInfo['all_tiers'] ?? [],
            ],
        ];
    }
}
