<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePayRollRequest;
use App\Http\Requests\UpdatePayRollRequest;
use App\Services\PayRollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayRollController extends Controller
{
    public function __construct(
        protected PayRollService $payRollService
    ){}
    /**
     * Display a listing of payrolls.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];

        $payRolls = $this->payRollService->getAllPayRolls($filters, true);

        return ResponseHelper::success($payRolls, 'Payrolls retrieved successfully');
    }

    /**
     * Store a newly created payroll.
     */
    public function store(StorePayRollRequest $request): JsonResponse
    {
        $payRoll = $this->payRollService->createPayRoll($request->validated());

        return ResponseHelper::created($payRoll, 'Payroll created successfully');
    }
    /**
     * assign periods to payroll
     */
    public function assignPeriods(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'period_ids'   => ['required', 'array'],
            'period_ids.*' => ['exists:periods,id'],
        ]);

        $payRoll = $this->payRollService->assignPeriods($id, $data['period_ids']);

        return ResponseHelper::success($payRoll, 'Periods assigned and type updated from Period settings');
    }

    /**
     * Display the specified payroll.
     */
    public function show(int $id): JsonResponse
    {
        $payRoll = $this->payRollService->getPayRollById($id);

        return ResponseHelper::success($payRoll, 'Payroll retrieved successfully');
    }

    /**
     * Update the specified payroll.
     */
    public function update(UpdatePayRollRequest $request, int $id): JsonResponse
    {
        $payRoll = $this->payRollService->updatePayRoll($id, $request->validated());

        return ResponseHelper::success($payRoll, 'Payroll updated successfully');
    }

    /**
     * Remove the specified payroll (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->payRollService->deletePayRoll($id);

        return ResponseHelper::success(null, 'Payroll deleted successfully');
    }

    /**
     * Restore soft deleted payroll.
     */
    public function restore(int $id): JsonResponse
    {
        $payRoll = $this->payRollService->restorePayRoll($id);

        return ResponseHelper::success($payRoll, 'Payroll restored successfully');
    }

    /**
     * Permanently delete payroll.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->payRollService->forceDeletePayRoll($id);

        return ResponseHelper::success(null, 'Payroll permanently deleted');
    }


}
