<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\BkashRequest;
use App\Services\BkashService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BkashController extends Controller
{
    public function __construct(protected BkashService $bkashService)
    {}
    public function grantToken(): JsonResponse
    {
        $result = $this->bkashService->grantToken();
        return ResponseHelper::success($result, 'Token granted successfully...');
    }
    public function createPayment(BkashRequest $request): JsonResponse
    {
        $result = $this->bkashService->createPayment($request->validated());
        return ResponseHelper::success($result, 'Payment Created successfully...');
    }
    public function execute(Request $request)
    {
        $paymentID = $request->query('paymentID');
        $status = $request->query('status');

        if (!$paymentID) {
            return ResponseHelper::error('Payment ID is required');
        }

        if ($status === 'success') {
            return redirect()->route('bkash.success', ['paymentID' => $paymentID]);
        }

        return redirect()->route('bkash.failure', ['paymentID' => $paymentID, 'status' => $status]);
    }
    public function successPayment(Request $request): JsonResponse
    {
        $paymentID = $request->query('paymentID');

        $result = $this->bkashService->execute($paymentID);
        $finalData = $this->bkashService->successStatus($result);

        return ResponseHelper::success($finalData, 'Payment Successful API Called');
    }

    public function failurePayment(Request $request): JsonResponse
    {
        $status = (string) $request->query('status', 'failed');
        $paymentID = $request->query('paymentID') ? (string) $request->query('paymentID') : null;

        $finalData = $this->bkashService->failureStatus($status, $paymentID);

        return ResponseHelper::error( "Payment Failure API Called. Status: $status", 400);
    }

}
