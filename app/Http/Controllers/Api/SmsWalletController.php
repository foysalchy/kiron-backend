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
    public function transactions(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $paginated = SmsWalletTransaction::with('reference')
            ->latest()
            ->paginate($perPage);

        $paginated->getCollection()->transform(function ($t) {
            $recipient = null;
            $recipientName = null;

            if ($t->reference instanceof \App\Models\Order) {
                $order = $t->reference;
                $recipient = $order->shipping_address['phone'] ?? $order->customer?->phone ?? null;
                $recipientName = $order->shipping_address['name'] ?? $order->customer?->name ?? null;
            } elseif ($t->reference instanceof \App\Models\SmsSend) {
                $smsSend = $t->reference;
                $customNumbers = $smsSend->custom_numbers ?? [];
                if (!empty($customNumbers)) {
                    $count = count($customNumbers);
                    $recipient = implode(', ', array_slice($customNumbers, 0, 2)) . ($count > 2 ? " (+{$count} more)" : "");
                }
            }

            $t->recipient = $recipient;
            $t->recipient_name = $recipientName;

            return $t;
        });

        return ResponseHelper::success($paginated);
    }
}
