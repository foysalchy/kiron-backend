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
    public function execute(Request $request): JsonResponse
    {
        $paymentID = $request->query('paymentID');
        $companyId = $request->get('company_id', 0); 

        if (!$paymentID) {
            return ResponseHelper::error('Payment ID is required to execute payment');
        }

        $result = $this->bkashService->execute($paymentID, (int) $companyId);
        
        return ResponseHelper::success($result, 'Payment executed successfully');
    }

}
