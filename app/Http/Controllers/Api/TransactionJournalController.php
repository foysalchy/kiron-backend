<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounts\StoreTransactionJournalRequest;
use App\Http\Requests\Accounts\UpdateTransactionJournalRequest;
use App\Services\TransactionJournalService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class TransactionJournalController extends Controller
{
    public function __construct(protected TransactionJournalService $journalService) {}

    /**
     * Get all journal transactions with filters and totals
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'    => $request->query('status'),    // Array expected for multi-select
            'range'     => $request->query('range'),     // e.g., "Last 7 Days", "February 2026"
            'from_date' => $request->query('from_date'),
            'to_date'   => $request->query('to_date'),
            'search'    => $request->query('search'),
            'per_page'  => $request->query('per_page', 25),
        ];

        $data = $this->journalService->getAllJournals($filters);

        return ResponseHelper::success($data, 'Journals retrieved successfully');
    }

    /**
     * Store a newly created journal entry.
     */
    public function store(StoreTransactionJournalRequest $request): JsonResponse
    {
        // Request handle hobar somoy-i validation hoye jabe (Debit == Credit)
        $data = $this->journalService->createJournal($request->validated());

        return ResponseHelper::success($data, 'Journal entry created successfully', 201);
    }

    /**
     * Display the specified journal entry.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->journalService->getJournalById($id);

        return ResponseHelper::success($data, 'Journal details retrieved successfully');
    }

    /**
     * Update an existing journal entry.
     */
    public function update(UpdateTransactionJournalRequest $request, int $id): JsonResponse
    {
        $data = $this->journalService->updateJournal($id, $request->validated());

        return ResponseHelper::success($data, 'Journal entry updated successfully');
    }

    /**
     * Soft delete the journal.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->journalService->deleteJournal($id);

        return ResponseHelper::success(null, 'Journal deleted successfully');
    }

    /**
     * Restore a soft-deleted journal.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->journalService->restoreJournal($id);

        return ResponseHelper::success($data, 'Journal restored successfully');
    }

    /**
     * Permanently delete the journal.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->journalService->forceDeleteJournal($id);

        return ResponseHelper::success(null, 'Journal permanently deleted');
    }

    /**
     * Handle status change (e.g., Draft to Approved)
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required'
        ]);

        $data = $this->journalService->updateStatus($id, $request->status);

        return ResponseHelper::success($data, 'Status updated to ' . $request->status . ' successfully');
    }
}
