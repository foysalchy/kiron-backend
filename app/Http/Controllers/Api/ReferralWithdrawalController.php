<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReferralWithdrawal;
use App\Services\ReferralService;
use Illuminate\Http\Request;

class ReferralWithdrawalController extends Controller
{
    protected ReferralService $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    public function index(Request $request)
    {
        $query = ReferralWithdrawal::with(['partner.group']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('partner_id')) {
            $query->where('referral_partner_id', $request->partner_id);
        }

        $withdrawals = $query->orderBy('id', 'desc')->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => 'success',
            'data' => $withdrawals,
        ]);
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'transaction_reference' => 'nullable|string|max:255',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        try {
            $withdrawal = $this->referralService->approveWithdrawal(
                (int)$id,
                $request->transaction_reference,
                $request->admin_notes
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Withdrawal request approved successfully',
                'data' => $withdrawal->load('partner'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        try {
            $withdrawal = $this->referralService->rejectWithdrawal(
                (int)$id,
                $request->admin_notes
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Withdrawal request rejected and amount refunded to partner wallet',
                'data' => $withdrawal->load('partner'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
