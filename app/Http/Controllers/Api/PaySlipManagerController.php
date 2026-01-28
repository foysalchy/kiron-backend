<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\GeneratePaySlipRequest;
use App\Services\PaySlipManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaySlipManagerController extends Controller
{
    public function __construct(protected PaySlipManagerService $paySlipManagerService) 
    {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();
        $records = $this->paySlipManagerService->getAllPaySlipManagers($filters);

        return ResponseHelper::success($records, 'Pay slip managers retrieved successfully');
    }
    /**
     * Display the specified pay slip manager details.
     */
    public function show(Request $request, int $id): JsonResponse
    {

        $record = $this->paySlipManagerService->getGeneratePayslipDetails($id, $request->only('period_id'));

        return ResponseHelper::success($record, 'Pay slip details retrieved');
    }
      /**
     * Store a newly 
     */
    public function store(GeneratePaySlipRequest $request): JsonResponse
    {
        $record = $this->paySlipManagerService->processBatchGeneration($request->validated());

        return ResponseHelper::created($record, 'Pay head assigned successfully');
    }
    /**
     * Regenerate payslips for employees
     */
    public function regenerate(GeneratePaySlipRequest $request): JsonResponse
    {
        $record = $this->paySlipManagerService->processBatchGeneration($request->validated(), true);

        return ResponseHelper::success($record, 'Payslips regenerated successfully');
    }
    /**
     * Get Salary Sheet
     */
    public function salarySheet(Request $request): JsonResponse
    {
        $records = $this->paySlipManagerService->getSalarySheetData($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Salary sheet retrieved successfully',
            'data' => $records
        ]);
    }
}
