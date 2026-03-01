<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounts\StoreRecurringJournalRequest;
use App\Http\Requests\Accounts\UpdateRecurringJournalRequest;
use App\Services\RecurringJournalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecurringJournalController extends Controller
{
    public function __construct(protected RecurringJournalService $recurringJournal){}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->recurringJournal->getAllJournals($request->all());
        return ResponseHelper::success($data, 'Recurring journals retrieved successfully');
    }
    /**
     * Store a newly created recurring journal with details.
     */
    public function store(StoreRecurringJournalRequest $request): JsonResponse
    {
        $data = $this->recurringJournal->createJournal($request->validated());

        return ResponseHelper::success($data, 'Recurring journal created successfully', 201);
    }

    /**
     * Display the specified recurring journal.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->recurringJournal->getJournalById($id);

        return ResponseHelper::success($data, 'Recurring journal details retrieved successfully');
    }
    /**
     * Update an existing record.
     */
    public function update(int $id, UpdateRecurringJournalRequest $request): JsonResponse
    {
         $data = $this->recurringJournal->updateJournal($id, $request->validated());

        return ResponseHelper::success($data, 'Recurring journal updated successfully');
    }
    /**
     * Remove the specified journal (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->recurringJournal->deleteJournal($id);

        return ResponseHelper::success(null, 'Recurring journal deleted successfully');
    }
    /**
     * Restore a soft-deleted recurring journal.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->recurringJournal->restoreJournal($id);

        return ResponseHelper::success($data, 'Recurring journal restored successfully');
    }

    /**
     * Permanently delete a recurring journal from the database.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->recurringJournal->forceDeleteJournal($id);

        return ResponseHelper::success(null, 'Recurring journal permanently deleted');
    }
    /**
     * Update Approval Status
     */
    public function updateApprovalStatus(Request $request, int $id): JsonResponse
    {
        $request->validate(['status' => 'required|string']);
        $data = $this->recurringJournal->updateApprovalStatus($id, $request->status);

        return ResponseHelper::success($data, 'Approval status updated');
    }

    /**
     * toggle status.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->recurringJournal->toggleStatus($id);

        return  ResponseHelper::success($data, 'Status updated successfully');
    }


}
