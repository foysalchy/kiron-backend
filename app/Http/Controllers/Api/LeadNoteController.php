<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadNoteRequest;
use App\Http\Requests\UpdateLeadNoteRequest;
use App\Services\LeadNoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadNoteController extends Controller
{
    public function __construct(protected LeadNoteService $leadNoteService)
    {
    }

    /**
     * Display a listing of lead notes.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'     => $request->query('status'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];

        $data = $this->leadNoteService->getAllLeadNotes($filters, true);

        return ResponseHelper::success($data, 'Lead notes retrieved successfully');
    }

    /**
     * Store a newly created lead note.
     */
    public function store(StoreLeadNoteRequest $request): JsonResponse
    {
        $note = $this->leadNoteService->createLeadNote($request->validated());

        return ResponseHelper::created($note, 'Note added to lead successfully');
    }

    /**
     * Display the specified lead note.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->leadNoteService->getNoteById($id);

        return ResponseHelper::success($data, 'Note details retrieved successfully');
    }

    /**
     * Update the specified lead note.
     */
    public function update(UpdateLeadNoteRequest $request, int $id): JsonResponse
    {
        $data = $this->leadNoteService->updateLeadNote($id, $request->validated());

        return ResponseHelper::success($data, 'Note updated successfully');
    }

    /**
     * Soft delete the lead note.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->leadNoteService->deleteLeadNote($id);

        return ResponseHelper::success(null, 'Note deleted successfully');
    }

    /**
     * Restore a soft deleted lead note.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->leadNoteService->restoreLeadNote($id);

        return ResponseHelper::success($data, 'Note restored successfully');
    }

    /**
     * Permanently delete the lead note.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->leadNoteService->forceDeleteLeadNote($id);

        return ResponseHelper::success(null, 'Note permanently removed');
    }
}
