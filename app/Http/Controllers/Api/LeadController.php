<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\{ StoreCompanyRequest, StorePartyRequest };
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Services\LeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function __construct(protected LeadService $leadService) {}

    /**
     * Display a listing of leads.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'         => $request->query('status'),
            'search'         => $request->query('search'),
            'lead_source_id' => $request->query('lead_source_id'),
            'source_name'    => $request->query('source_name'),
            'sort_by'        => $request->query('sort_by', 'created_at'),
            'sort_order'     => $request->query('sort_order', 'desc'),
            'per_page'       => $request->query('per_page', 15),
        ];

        $data = $this->leadService->getAllLeads($filters, true);

        return ResponseHelper::success($data, 'Leads retrieved successfully');
    }

    /**
     * Store a newly created lead.
     */
    public function store(StoreLeadRequest $request): JsonResponse
    {
        $lead = $this->leadService->createLead($request->validated());

        return ResponseHelper::created($lead, 'Lead created successfully');
    }

    /**
     * Display the specified lead.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->leadService->getLeadById($id);

        return ResponseHelper::success($data, 'Lead retrieved successfully');
    }

    /**
     * Update the specified lead.
     */
    public function update(UpdateLeadRequest $request, int $id): JsonResponse
    {
        $data = $this->leadService->updateLead($id, $request->validated());

        return ResponseHelper::success($data, 'Lead updated successfully');
    }

    /**
     * Soft delete the lead.
     */
    public function convertToSeller(StoreCompanyRequest $request, int $id): JsonResponse
    {
        $this->leadService->convertToSeller($request->validated(), $id);

        return ResponseHelper::success(null, 'Converted to seller done succsessfully');
    }
    public function convertToCustomer(StorePartyRequest $request, int $id): JsonResponse
    {
        $this->leadService->convertToCustomer($request->validated(), $id);

        return ResponseHelper::success(null, 'Converted to customer done succsessfully');
    }
    /**
     * Soft delete the lead.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->leadService->deleteLead($id);

        return ResponseHelper::success(null, 'Lead deleted successfully');
    }

    /**
     * Restore a soft deleted lead.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->leadService->restoreLead($id);

        return ResponseHelper::success($data, 'Lead restored successfully');
    }
    /**
     * Permanently delete the lead.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->leadService->forceDeleteLead($id);

        return ResponseHelper::success(null, 'Lead permanently deleted from database');
    }

    /**
     * Toggle Lead status (Active/Inactive)
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->leadService->toggleStatus($id);

        return ResponseHelper::success($data, 'Lead status updated successfully');
    }
}

