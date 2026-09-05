<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\ReferralAttribution;
use App\Models\ReferralCommission;
use App\Models\ReferralGroup;
use App\Models\ReferralPartner;
use App\Models\ReferralWithdrawal;
use App\Models\User;
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
        $partner = ReferralPartner::with('group')->where('referral_code', $code)->where('status', 1)->first();

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
     * Attribute a company registration to a referral partner.
     */
    public function attributeReferral(int $partnerId, int $companyId, string $code, float $discountRate = 0, float $discountAmount = 0): ReferralAttribution
    {
        return ReferralAttribution::firstOrCreate(
            ['company_id' => $companyId],
            [
                'referral_partner_id'   => $partnerId,
                'referral_code_used'    => $code,
                'buyer_discount_rate'   => $discountRate,
                'buyer_discount_amount' => $discountAmount,
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
            if (!$partner || $partner->status != 1) {
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
            $attribution->update(['status' => 'subscribed_active']);

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
        $defaultGroup = ReferralGroup::where('status', 1)->first();

        // Generate unique code (e.g. REF-XYZ123)
        $code = 'REF-' . strtoupper(Str::random(6));
        while (ReferralPartner::where('referral_code', $code)->exists()) {
            $code = 'REF-' . strtoupper(Str::random(6));
        }

        return ReferralPartner::create([
            'referral_group_id' => $defaultGroup?->id,
            'name'              => $data['name'],
            'email'             => $data['email'],
            'phone'             => $data['phone'] ?? null,
            'password'          => Hash::make($data['password']),
            'referral_code'     => $code,
            'payout_method'     => $data['payout_method'] ?? 'bkash',
            'payout_details'    => $data['payout_details'] ?? null,
            'status'            => 1,
        ]);
    }

    /**
     * Partner submits a withdrawal request.
     */
    public function requestWithdrawal(ReferralPartner $partner, array $data): ReferralWithdrawal
    {
        $amount = floatval($data['amount']);
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
                'payout_method'       => $data['payout_method'] ?? ($partner->payout_method ?: 'bkash'),
                'account_details'     => $data['account_details'] ?? $partner->payout_details,
                'status'              => 'pending',
                'admin_note'          => $data['note'] ?? null,
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
    public function approveWithdrawal(int $withdrawalId, User $admin, array $data): ReferralWithdrawal
    {
        DB::beginTransaction();
        try {
            $withdrawal = ReferralWithdrawal::with('partner')->findOrFail($withdrawalId);
            if ($withdrawal->status !== 'pending') {
                throw new \Exception('This withdrawal request is already ' . $withdrawal->status);
            }

            $withdrawal->update([
                'status'                => 'approved',
                'processed_by'          => $admin->id,
                'processed_at'          => Carbon::now(),
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'admin_note'            => $data['admin_note'] ?? null,
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
    public function rejectWithdrawal(int $withdrawalId, User $admin, array $data): ReferralWithdrawal
    {
        DB::beginTransaction();
        try {
            $withdrawal = ReferralWithdrawal::with('partner')->findOrFail($withdrawalId);
            if ($withdrawal->status !== 'pending') {
                throw new \Exception('This withdrawal request is already ' . $withdrawal->status);
            }

            $withdrawal->update([
                'status'       => 'rejected',
                'processed_by' => $admin->id,
                'processed_at' => Carbon::now(),
                'admin_note'   => $data['admin_note'] ?? ($data['reason'] ?? 'Rejected by admin'),
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
     * Get aggregated stats for a partner's portal dashboard.
     */
    public function getPartnerDashboardData(ReferralPartner $partner): array
    {
        $partner->load('group.tiers');

        $totalAttributions = $partner->attributions()->count();

        // Count active vs inactive referred companies based on company status or subscription
        $activeCount = $partner->attributions()
            ->whereHas('company', function ($q) {
                $q->where('status', Status::Active->value);
            })->count();

        $inactiveCount = max(0, $totalAttributions - $activeCount);

        $recentAttributions = $partner->attributions()
            ->with(['company.subscription.pricingPackage', 'company.user'])
            ->latest()
            ->take(10)
            ->get();

        $recentCommissions = $partner->commissions()
            ->with('company')
            ->latest()
            ->take(10)
            ->get();

        $tierProgress = $partner->getCurrentCommissionRate();

        return [
            'partner'             => $partner,
            'total_referrals'     => $totalAttributions,
            'active_referrals'    => $activeCount,
            'inactive_referrals'  => $inactiveCount,
            'wallet_balance'      => $partner->wallet_balance,
            'total_earned'        => $partner->total_earned,
            'total_withdrawn'     => $partner->total_withdrawn,
            'tier_progress'       => $tierProgress,
            'recent_attributions' => $recentAttributions,
            'recent_commissions'  => $recentCommissions,
        ];
    }
}
