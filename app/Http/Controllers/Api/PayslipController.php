<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PayslipService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PayslipController extends Controller
{
    public function __construct(protected PayslipService $payslipService) {}

    /**
     * Generate Payslips for selected employees for a specific period
     */
    public function generate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'period_id' => ['required', 'exists:periods,id'],
            'employee_ids' => ['required', 'array'],
            'employee_ids.*' => ['exists:employees,id']
        ]);

        $result = $this->payslipService->generatePayslips($data['period_id'], $data['employee_ids']);

        if (!$result['success']) {
            return ResponseHelper::error($result['message']);
        }

        return ResponseHelper::success(null, $result['message']);
    }
    public function previewSummary(Request $request): JsonResponse
    {
        $data = $request->validate([
            'period_id' => 'required|exists:periods,id',
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id'
        ]);

        $result = $this->payslipService->previewSummary($data['period_id'], $data['employee_ids']);

        return ResponseHelper::success($result, 'Preview data fetched');
    }
    /**
     * Get list of generated payslips (for listing page)
     */
    public function index(Request $request): JsonResponse
    {
        $payslips = $this->payslipService->getAllPayslips($request->all());
        return ResponseHelper::success($payslips, 'Payslips retrieved successfully');
    }

    /**
     * View specific payslip details
     */
    public function show($id): JsonResponse
    {
        $payslip = $this->payslipService->getPayslipDetails($id);
        return ResponseHelper::success($payslip, 'Payslip details retrieved');
    }
}
