<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourierRequest;
use App\Http\Requests\UpdateCourierRequest;
use App\Services\CourierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourierController extends Controller
{
    public function __construct(
        protected CourierService $courierService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'            => $request->query('status'),
            'name' => $request->query('name'),
            'search'            => $request->query('search'),
            'sort_by'           => $request->query('sort_by', 'created_at'),
            'sort_order'        => $request->query('sort_order', 'desc'),
            'per_page'          => $request->query('per_page', 15),
        ];

        $data = $this->courierService->getAllCouriers($filters, true);

        return ResponseHelper::success($data, 'Couriers retrieved successfully');
    }

    public function store(StoreCourierRequest $request): JsonResponse
    {
        $data = $this->courierService->createCourier($request->validated());

        return ResponseHelper::success($data, 'Courier created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->courierService->getCourierById($id);

        return ResponseHelper::success($data, 'Courier retrieved successfully');
    }

    public function update(UpdateCourierRequest $request, int $id): JsonResponse
    {
        $data = $this->courierService->updateCourier($id, $request->validated());

        return ResponseHelper::success($data, 'Courier updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->courierService->deleteCourier($id);

        return ResponseHelper::success(null, 'Courier deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $data = $this->courierService->restoreCourier($id);

        return ResponseHelper::success($data, 'Courier restored successfully');
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $this->courierService->forceDeleteCourier($id);

        return ResponseHelper::success(null, 'Courier permanently deleted');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->courierService->toggleStatus($id);

        return ResponseHelper::success($data, 'Courier status updated successfully');
    }
}
