<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounts\StoreTransactionIncomeRequest;
use App\Http\Requests\Accounts\UpdateTransactionIncomeRequest;
use App\Services\TransactionIncomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionIncomeController extends Controller
{
    public function __construct(protected TransactionIncomeService $incomeService){}
    /**
     * Get all transaction incomes with filters
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'income_to_id'   => $request->query('income_to_id'),  
            'income_from_id' => $request->query('income_from_id'),
            'status'       => $request->query('status'), // Expects array for multi-select
            'range'        => $request->query('range'),  // e.g., "Last 7 Days", "January 2026"
            'from_date'    => $request->query('from_date'),
            'to_date'      => $request->query('to_date'),
            'search'       => $request->query('search'),
            'per_page'     => $request->query('per_page', 25),
        ];

        $data = $this->incomeService->getAllIncomes($filters);

        return ResponseHelper::success($data, 'Incomes retrieved successfully');
    }

    /**
     * Store a newly created income with categories.
     */
    public function store(StoreTransactionIncomeRequest $request): JsonResponse
    {
        $data = $this->incomeService->createIncome($request->validated());

        return ResponseHelper::success($data, 'Income transaction created successfully', 201);
    }

    /**
     * Display the specified income.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->incomeService->getIncomeById($id);

        return ResponseHelper::success($data, 'Income details retrieved successfully');
    }

    /**
     * Update transaction income
     */
    public function update(UpdateTransactionIncomeRequest $request, int $id): JsonResponse
    {
        $data = $this->incomeService->updateIncome($id, $request->validated());

        return ResponseHelper::success($data, 'Income transaction updated successfully');
    }

    /**
     * Soft delete the transaction income.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->incomeService->deleteIncome($id);

        return ResponseHelper::success(null, 'Income record deleted successfully');
    }

    /**
     * Restore soft-deleted transaction income.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->incomeService->restoreIncome($id);

        return ResponseHelper::success($data, 'Income record restored successfully');
    }

    /**
     * Permanently delete the transaction income.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->incomeService->forceDeleteIncome($id);

        return ResponseHelper::success(null, 'Income record permanently deleted');
    }

    /**
     * Handle status change request
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required'
        ]);

        $data = $this->incomeService->updateStatus($id, $request->status);

        return ResponseHelper::success($data, 'Status updated to ' . $request->status . ' successfully');
    }
}
