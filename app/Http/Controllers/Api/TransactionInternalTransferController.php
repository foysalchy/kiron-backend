<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounts\StoreTransactionInternalTransferRequest;
use App\Http\Requests\Accounts\UpdateTransactionInternalTransferRequest;
use App\Services\TransactionInternalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionInternalTransferController extends Controller
{
    public function __construct(protected TransactionInternalService $transferService) {}

    /**
     * Get all internal transfers with filters
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'from_account_id' => $request->query('from_account_id'),
            'to_account_id'   => $request->query('to_account_id'),
            'status'          => $request->query('status'), // Expects array for multi-select
            'range'           => $request->query('range'),  // e.g., "Last 7 Days", "January 2026"
            'from_date'       => $request->query('from_date'),
            'to_date'         => $request->query('to_date'),
            'search'          => $request->query('search'),
            'per_page'        => $request->query('per_page', 25),
        ];

        $data = $this->transferService->getAllTransfers($filters);

        return ResponseHelper::success($data, 'Transfers retrieved successfully');
    }

    /**
     * Store a newly created internal transfer with details.
     */
    public function store(StoreTransactionInternalTransferRequest $request): JsonResponse
    {
        $data = $this->transferService->createTransfer($request->validated());

        return ResponseHelper::success($data, 'Transfer created successfully', 201);
    }

    /**
     * Display the specified internal transfer.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->transferService->getTransferById($id);

        return ResponseHelper::success($data, 'Transfer details retrieved successfully');
    }

    /**
     * Update internal transfer (Sync Logic)
     */
    public function update(UpdateTransactionInternalTransferRequest $request, int $id): JsonResponse
    {
        $data = $this->transferService->updateTransfer($id, $request->validated());

        return ResponseHelper::success($data, 'Transfer updated successfully');
    }

    /**
     * Soft delete the internal transfer.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->transferService->deleteTransfer($id);

        return ResponseHelper::success(null, 'Transfer deleted successfully');
    }

    /**
     * Restore soft-deleted internal transfer.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->transferService->restoreTransfer($id);

        return ResponseHelper::success($data, 'Transfer restored successfully');
    }

    /**
     * Permanently delete the internal transfer.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->transferService->forceDeleteTransfer($id);

        return ResponseHelper::success(null, 'Transfer permanently deleted');
    }

    /**
     * Handle status change request
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required'
        ]);

        $data = $this->transferService->updateStatus($id, $request->status);

        return ResponseHelper::success($data, 'Status updated to ' . $request->status . ' successfully');
    }
}
