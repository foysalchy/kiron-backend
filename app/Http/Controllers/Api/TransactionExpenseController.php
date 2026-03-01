<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounts\StoreTransactionExpenseRequest;
use App\Http\Requests\Accounts\UpdateTransactionExpenseRequest;
use App\Services\TransactionExpenseService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class TransactionExpenseController extends Controller
{
    public function __construct(protected TransactionExpenseService $expenseService) {}

    /**
     * Get all period types with filters
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'expense_from_id' => $request->query('expense_from_id'),
            'expense_to_id'   => $request->query('expense_to_id'),
            'status'          => $request->query('status'), // Expects array for multi-select
            'range'           => $request->query('range'),  // e.g., "Last 7 Days", "January 2026"
            'from_date'       => $request->query('from_date'),
            'to_date'         => $request->query('to_date'),
            'search'          => $request->query('search'),
            'per_page'        => $request->query('per_page', 25),
        ];

        $data = $this->expenseService->getAllExpenses($filters);

        return ResponseHelper::success($data, 'Expenses retrieved successfully');
    }
    /**
     * Store a newly created expense with categories.
     */
    public function store(StoreTransactionExpenseRequest $request): JsonResponse
    {
        $data = $this->expenseService->createExpense($request->validated());

        return ResponseHelper::success($data, 'Expense created successfully', 201);
    }

    /**
     * Display the specified expense.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->expenseService->getExpenseById($id);

        return ResponseHelper::success($data, 'Expense details retrieved successfully');
    }
    /**
     * Update period type
     */
    public function update(UpdateTransactionExpenseRequest $request, int $id): JsonResponse
    {
        $data = $this->expenseService->updateExpense($id, $request->validated());

        return ResponseHelper::success($data, 'Expense updated successfully');
    }
    /**
     * Soft delete the transaction expense.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->expenseService->deleteExpense($id);

        return ResponseHelper::success(null, 'Expense deleted successfully');
    }

    /**
     * Restore soft-deleted transaction expense.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->expenseService->restoreExpense($id);

        return ResponseHelper::success($data, 'Expense restored successfully');
    }

    /**
     * Permanently delete the transaction expense.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->expenseService->forceDeleteExpense($id);

        return ResponseHelper::success(null, 'Expense permanently deleted');
    }
    /**
     * Handle status change request
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required'
        ]);

        $data = $this->expenseService->updateStatus($id, $request->status);

        return ResponseHelper::success($data, 'Status updated to ' . $request->status . ' successfully');
    }
}
