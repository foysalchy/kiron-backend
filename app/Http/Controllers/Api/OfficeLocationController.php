<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOfficeLocationRequest;
use App\Http\Requests\UpdateOfficeLocationRequest;
use App\Services\OfficeLocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfficeLocationController extends Controller
{
    public function __construct(
        protected OfficeLocationService $locationService
    ) {}
    /**
     * Display a listing of office locations.
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

        $data = $this->locationService->getAllLocations($filters, true);

        return ResponseHelper::success($data, 'Office locations retrieved successfully');
    }

    /**
     * Store a newly created office location.
     */
    public function store(StoreOfficeLocationRequest $request): JsonResponse
    {
        // $request->validated() 
        $data = $this->locationService->createLocation($request->validated());

        return ResponseHelper::success($data, 'Office location created successfully', 201);
    }

    /**
     * Display the specified office location.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->locationService->getLocationById($id);

        return ResponseHelper::success($data, 'Office location retrieved successfully');
    }

    /**
     * Update the specified office location.
     */
    public function update(UpdateOfficeLocationRequest $request, int $id): JsonResponse
    {
        $data = $this->locationService->updateLocation($id, $request->validated());

        return ResponseHelper::success($data, 'Office location updated successfully');
    }

    /**
     * Remove the specified office location (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->locationService->deleteLocation($id);

        return ResponseHelper::success(null, 'Office location deleted successfully');
    }

    /**
     * Restore a soft-deleted office location.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->locationService->restoreLocation($id);

        return ResponseHelper::success($data, 'Office location restored successfully');
    }

    /**
     * Permanently delete an office location.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->locationService->forceDeleteLocation($id);

        return ResponseHelper::success(null, 'Office location permanently deleted');
    }

    /**
     * Toggle the status of an office location.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->locationService->toggleStatus($id);

        return ResponseHelper::success($data, 'Office location status updated successfully');
    }
}
