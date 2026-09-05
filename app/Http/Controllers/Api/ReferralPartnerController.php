<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReferralPartner;
use Illuminate\Http\Request;

class ReferralPartnerController extends Controller
{
    public function index(Request $request)
    {
        $query = ReferralPartner::with(['group.tiers'])
            ->withCount([
                'attributions as total_referrals_count',
                'attributions as active_referrals_count' => function ($q) {
                    $q->whereHas('company', function ($sub) {
                        $sub->where('status', 1);
                    });
                },
                'attributions as inactive_referrals_count' => function ($q) {
                    $q->where(function ($w) {
                        $w->whereDoesntHave('company')
                          ->orWhereHas('company', function ($sub) {
                              $sub->where('status', '!=', 1);
                          });
                    });
                },
            ]);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('referral_code', 'like', "%{$s}%");
            });
        }

        if ($request->filled('group_id')) {
            $query->where('referral_group_id', $request->group_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $partners = $query->orderBy('id', 'desc')->paginate($request->per_page ?? 15);

        // Append current dynamic commission rate for each partner
        $partners->getCollection()->transform(function ($partner) {
            $partner->current_commission_rate = $partner->getCurrentCommissionRate();
            return $partner;
        });

        return response()->json([
            'status' => 'success',
            'data' => $partners,
        ]);
    }

    public function show($id)
    {
        $partner = ReferralPartner::with([
            'group.tiers',
            'attributions.company',
            'commissions.company',
            'withdrawals',
        ])->findOrFail($id);

        $partner->current_commission_rate = $partner->getCurrentCommissionRate();

        return response()->json([
            'status' => 'success',
            'data' => $partner,
        ]);
    }

    public function update(Request $request, $id)
    {
        $partner = ReferralPartner::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'referral_group_id' => 'sometimes|nullable|exists:referral_groups,id',
            'status' => 'sometimes|in:active,inactive,suspended',
            'custom_commission_rate' => 'sometimes|nullable|numeric|min:0|max:100',
            'phone' => 'sometimes|nullable|string|max:50',
            'payout_method' => 'sometimes|nullable|string|max:50',
            'payout_details' => 'sometimes|nullable|string|max:500',
        ]);

        $partner->update($request->only([
            'name',
            'referral_group_id',
            'status',
            'custom_commission_rate',
            'phone',
            'payout_method',
            'payout_details',
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Partner updated successfully',
            'data' => $partner->fresh(['group']),
        ]);
    }

    public function destroy($id)
    {
        $partner = ReferralPartner::findOrFail($id);
        $partner->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Partner deleted successfully',
        ]);
    }
}
