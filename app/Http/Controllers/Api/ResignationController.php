<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResignationRequest;
use App\Services\ResignationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResignationController extends Controller
{
    public function __construct(
        protected ResignationService $resignationService
    ) {}

    /**
     * Get all resignations with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'search'     => $request->query('search'),
            'status'     => $request->query('status'),
            'type'       => $request->query('type'), // resignation or termination
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];

        $data = $this->resignationService->getAllResignations($filters, true);

        return ResponseHelper::success($data, 'Resignations retrieved successfully');
    }

    /**
     * Store a newly created resignation.
     */
    public function store(StoreResignationRequest $request): JsonResponse
    {
        $data = $this->resignationService->createResignation($request->validated());

        return ResponseHelper::created($data, 'Resignation record created successfully');
    }

    /**
     * Display the specified resignation.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->resignationService->getResignationById($id);

        return ResponseHelper::success($data, 'Resignation details retrieved successfully');
    }
    /**
     * Toggle status (Active/Inactive).
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->resignationService->toggleStatus($id);

        return ResponseHelper::success($data, 'Resignation status updated successfully');
    }
}
