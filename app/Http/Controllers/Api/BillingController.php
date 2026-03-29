<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\Company;
use App\Services\BillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function __construct(protected BillingService $billingService)
    {}
    public function billingReports(int $id, Request $request): JsonResponse
    {
        Company::findOrFail($id);

        $reports = $this->billingService->getBillingReports($id, $request->all());

        return ResponseHelper::success($reports, 'Billing reports retrieved');
    }

}
