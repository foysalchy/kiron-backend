<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSmsRechargeRequest;
use App\Models\SmsPackage;
use App\Models\SmsRecharge;
use App\Models\SmsWalletTransaction;
use App\Services\SmsWalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmsWalletController extends Controller
{
    public function __construct(private SmsWalletService $service) {}

    public function wallet(): JsonResponse
    {

        $wallet    = $this->service->getOrCreate();
        return ResponseHelper::success($wallet);
    }


    public function packages(): JsonResponse
    {
        $packages = SmsPackage::where('status', Status::Active->value)->latest()->get();
        return ResponseHelper::success($packages);
    }

    public function requestRecharge(StoreSmsRechargeRequest $request): JsonResponse
    {
        $recharge = $this->service->requestRecharge(
            $request->validated()
        );
        return ResponseHelper::success($recharge, 'Recharge request submitted', 201);
    }

    // Recharge history
    public function rechargeHistory(): JsonResponse
    {
        $history = SmsRecharge::with('package')
            ->latest()
            ->get();
        return ResponseHelper::success($history);
    }

    // Transaction history
    public function transactions(): JsonResponse
    {
        $transactions = SmsWalletTransaction::latest()
            ->get();
        return ResponseHelper::success($transactions);
    }
}
