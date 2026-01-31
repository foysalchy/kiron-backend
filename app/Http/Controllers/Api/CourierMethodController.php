<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourierMethodRequest;
use App\Http\Requests\UpdateCourierMethodRequest;
use App\Services\CourierMethodService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourierMethodController extends Controller
{
    public function __construct(protected CourierMethodService $courierMethodService)
    {
    }
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'     => $request->query('status'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];

        $data = $this->courierMethodService->getAllCourierMethods($filters, true);

        return ResponseHelper::success($data, 'Courier methods retrieved successfully');
    }

    public function store(StoreCourierMethodRequest $request): JsonResponse
    {
        $data = $this->courierMethodService->createMethod($request->validated());

        return ResponseHelper::success($data, 'Courier method created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->courierMethodService->getMethodById($id);

        return ResponseHelper::success($data, 'Courier method retrieved successfully');
    }

    public function update(UpdateCourierMethodRequest $request, int $id): JsonResponse
    {
        $data = $this->courierMethodService->updateMethod($id, $request->validated());

        return ResponseHelper::success($data, 'Courier method updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->courierMethodService->deleteMethod($id);

        return ResponseHelper::success(null, 'Courier method deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $data = $this->courierMethodService->restoreMethod($id);

        return ResponseHelper::success($data, 'Courier method restored successfully');
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $this->courierMethodService->forceDeleteMethod($id);

        return ResponseHelper::success(null, 'Courier method permanently deleted');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->courierMethodService->toggleStatus($id);

        return ResponseHelper::success($data, 'Courier method status updated successfully');
    }
}
