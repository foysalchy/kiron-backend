<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveRechargeRequest;
use App\Models\SmsPackage;
use App\Models\SmsRecharge;
use App\Models\SmsWalletTransaction;
use App\Services\SmsWalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmsRechargeController extends Controller
{
    public function __construct(private SmsWalletService $service) {}


    public function index(): JsonResponse
    {
        $query = SmsRecharge::query()
            ->with(['company', 'package', 'approvedBy'])
            ->latest();

        if (auth()->user()->isSuperAdmin()) {
            $query->withoutGlobalScopes();
        }

        $recharges = $query->paginate(20);

        return ResponseHelper::success($recharges);
    }

    // Approve / Reject
    public function process(ApproveRechargeRequest $request, int $id): JsonResponse
    {
        $recharge = $this->service->processRecharge(
            $id,
            $request->validated(),
            auth()->id()
        );
        $msg = $recharge->status == Status::Approved->value ? 'Recharge approved' : 'Recharge rejected';
        return ResponseHelper::success($recharge, $msg);
    }

    // Reverse Approved Recharge
    public function reverse(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $recharge = $this->service->reverseRecharge(
            $id,
            $validated['reason'],
            auth()->id()
        );

        return ResponseHelper::success($recharge, 'Recharge approval reversed successfully');
    }
}
