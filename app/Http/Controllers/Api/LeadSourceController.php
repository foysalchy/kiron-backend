<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadSourceRequest;
use App\Http\Requests\UpdateLeadSourceRequest;
use App\Services\LeadSourceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadSourceController extends Controller
{
    public function __construct(protected LeadSourceService $leadSourceService)
    {
    }

    /**
     * Display a listing of lead sources.
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

        $data = $this->leadSourceService->getAllLeadSources($filters, true);

        return ResponseHelper::success($data, 'Lead sources retrieved successfully');
    }

    /**
     * Store a newly created lead source.
     */
    public function store(StoreLeadSourceRequest $request): JsonResponse
    {
        $leadSource = $this->leadSourceService->createLeadSource($request->validated());

        return ResponseHelper::created($leadSource, 'Lead source created successfully');
    }

    /**
     * Display the specified lead source.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->leadSourceService->getLeadSourceById($id);

        return ResponseHelper::success($data, 'Lead source retrieved successfully');
    }

    /**
     * Update the specified lead source.
     */
    public function update(UpdateLeadSourceRequest $request, int $id): JsonResponse
    {
        $data = $this->leadSourceService->updateLeadSource($id, $request->validated());

        return ResponseHelper::success($data, 'Lead source updated successfully');
    }

    /**
     * Soft delete the lead source.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->leadSourceService->deleteLeadSource($id);

        return ResponseHelper::success(null, 'Lead source deleted successfully');
    }

    /**
     * Restore a soft deleted lead source.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->leadSourceService->restoreLeadSource($id);

        return ResponseHelper::success($data, 'Lead source restored successfully');
    }

    /**
     * Permanently delete the lead source.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->leadSourceService->forceDeleteLeadSource($id);

        return ResponseHelper::success(null, 'Lead source permanently deleted');
    }

    /**
     * Toggle Lead Source status (Active/Inactive)
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->leadSourceService->toggleStatus($id);

        return ResponseHelper::success($data, 'Lead source status updated successfully');
    }
}
