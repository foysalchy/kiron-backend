<?php

namespace App\Http\Controllers\Api;

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

    // সব pending recharge দেখো
    public function index(): JsonResponse
    {
        $recharges = SmsRecharge::with(['company', 'package', 'approvedBy'])
            ->latest()
            ->paginate(20);
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
        $msg = $recharge->status === 1 ? 'Recharge approved' : 'Recharge rejected';
        return ResponseHelper::success($recharge, $msg);
    }
}
