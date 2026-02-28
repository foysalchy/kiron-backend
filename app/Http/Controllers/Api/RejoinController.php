<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRejoinRequest;
use App\Http\Requests\UpdateRejoinRequest;
use App\Services\RejoinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RejoinController extends Controller
{
     public function __construct(
        protected RejoinService $rejoinService
    ) {}
    /**
     * Display a listing of rejoin records.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'rejoin_date'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];

        $data = $this->rejoinService->getAllRejoins($filters, true);

        return ResponseHelper::success($data, 'Rejoin records retrieved successfully');
    }

    /**
     * Store a newly created rejoin record.
     */
    public function store(StoreRejoinRequest $request): JsonResponse
    {
         $data = $this->rejoinService->createRejoin($request->validated());

        return ResponseHelper::success($data, 'Employee rejoined successfully', 201);
    }
    /**
     * Update an existing rejoin record.
     */
    public function update(int $id, UpdateRejoinRequest $request): JsonResponse
    {
         $data = $this->rejoinService->updateRejoin($id, $request->validated());

        return ResponseHelper::success($data, 'Employee rejoin record updated successfully');
    }

    /**
     * Display the specified rejoin record.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->rejoinService->getRejoinById($id);

        return ResponseHelper::success($data, 'Rejoin record retrieved successfully');
    }
}
