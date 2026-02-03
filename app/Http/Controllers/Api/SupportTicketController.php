<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupportTicketRequest;
use App\Http\Requests\UpdateSupportTicketRequest;
use App\Services\SupportTicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function __construct(
        protected SupportTicketService $ticketService
    ) {}

    /**
     * Display a listing of tickets based on UI filters.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->ticketService->getAllTickets($request->all());

        return ResponseHelper::success($data, 'Support tickets retrieved successfully');
    }

    /**
     * Store a newly created ticket.
     */
    public function store(StoreSupportTicketRequest $request): JsonResponse
    {
        $data = $this->ticketService->createTicket($request->validated());

        return ResponseHelper::success($data, 'Support ticket created successfully', 201);
    }

    /**
     * Display the specified ticket.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->ticketService->getTicketById($id);

        return ResponseHelper::success($data, 'Support ticket retrieved successfully');
    }

    /**
     * Update the specified ticket (including image replacement).
     */
    public function update(UpdateSupportTicketRequest $request, int $id): JsonResponse
    {
        $data = $this->ticketService->updateTicket($id, $request->validated());

        return ResponseHelper::success($data, 'Support ticket updated successfully');
    }

    /**
     * Soft delete a ticket.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->ticketService->deleteTicket($id);

        return ResponseHelper::success(null, 'Support ticket deleted successfully');
    }

    /**
     * Restore a soft-deleted ticket.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->ticketService->restoreTicket($id);

        return ResponseHelper::success($data, 'Support ticket restored successfully');
    }

    /**
     * Permanently delete a ticket.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->ticketService->forceDeleteTicket($id);

        return ResponseHelper::success(null, 'Support ticket permanently deleted');
    }

    /**
     * Toggle ticket status (Active/Inactive or Open/Closed).
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->ticketService->toggleStatus($id);

        return ResponseHelper::success($data, 'Support ticket status updated successfully');
    }
}
