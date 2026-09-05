<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SubscriptionRefundRequest;
use App\Models\CompanySubscription;
use App\Models\ReferralCommission;
use App\Models\ReferralPartner;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionRefundController extends Controller
{
    // Superadmin: View all refund requests
    public function index(Request $request)
    {
        $refunds = SubscriptionRefundRequest::with(['company', 'subscription.pricingPackage'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => 'success',
            'data' => $refunds
        ]);
    }

    // Tenant/Company: Request a refund
    public function store(Request $request)
    {
        $request->validate([
            'company_subscription_id' => 'required|exists:company_subscriptions,id',
            'reason' => 'required|string',
        ]);

        $subscription = CompanySubscription::where('id', $request->company_subscription_id)
            ->where('company_id', auth()->user()->company_id)
            ->firstOrFail();

        // Ensure it's within 7 days
        if ($subscription->created_at->diffInDays(Carbon::now()) > 7) {
            return response()->json([
                'status' => 'error',
                'message' => 'Refund period (7 days) has expired for this subscription.'
            ], 400);
        }

        // Check if a request already exists
        $existing = SubscriptionRefundRequest::where('company_subscription_id', $subscription->id)->first();
        if ($existing) {
            return response()->json([
                'status' => 'error',
                'message' => 'A refund request already exists for this subscription.'
            ], 400);
        }

        $refundRequest = SubscriptionRefundRequest::create([
            'company_id' => auth()->user()->company_id,
            'company_subscription_id' => $subscription->id,
            'amount' => $subscription->amount_paid,
            'reason' => $request->reason,
            'status' => 'pending'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Refund requested successfully.',
            'data' => $refundRequest
        ]);
    }

    // Superadmin: Approve/Reject refund
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string'
        ]);

        $refundRequest = SubscriptionRefundRequest::findOrFail($id);

        if ($refundRequest->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only pending requests can be updated.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $refundRequest->status = $request->status;
            $refundRequest->admin_note = $request->admin_note;
            $refundRequest->save();

            if ($request->status === 'approved') {
                // Find associated pending referral commission
                $commission = ReferralCommission::where('company_subscription_id', $refundRequest->company_subscription_id)
                    ->where('status', 'pending')
                    ->first();

                if ($commission) {
                    $commission->status = 'reversed';
                    $commission->notes = $commission->notes . ' | Cancelled due to refund';
                    $commission->save();

                    $partner = ReferralPartner::find($commission->referral_partner_id);
                    if ($partner) {
                        $partner->decrement('pending_balance', $commission->commission_amount);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Refund request ' . $request->status,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process refund request: ' . $e->getMessage()
            ], 500);
        }
    }
}
