<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerPaymentMethodRequest;
use App\Http\Requests\UpdateCustomerPaymentMethodRequest;
use App\Services\CustomerPaymentMethodService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerPaymentMethodController extends Controller
{
    public function __construct(
        protected CustomerPaymentMethodService $paymentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'      => $request->query('status'),
            'customer_id' => $request->query('customer_id'),
            'search'      => $request->query('search'),
            'sort_by'     => $request->query('sort_by', 'created_at'),
            'sort_order'  => $request->query('sort_order', 'desc'),
            'per_page'    => $request->query('per_page', 15),
        ];

        $data = $this->paymentService->getAllCustomerPaymentMethods($filters, true);

        return ResponseHelper::success($data, 'Customer payment methods retrieved successfully');
    }

    public function store(StoreCustomerPaymentMethodRequest $request): JsonResponse
    {
        $data = $this->paymentService->createCustomerPaymentMethod($request->validated());

        return ResponseHelper::success($data, 'Payment method created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->paymentService->getCustomerPaymentMethodById($id);

        return ResponseHelper::success($data, 'Payment method retrieved successfully');
    }

    public function update(UpdateCustomerPaymentMethodRequest $request, int $id): JsonResponse
    {
        $data = $this->paymentService->updateCustomerPaymentMethod($id, $request->validated());

        return ResponseHelper::success($data, 'Payment method updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->paymentService->deleteCustomerPaymentMethod($id);

        return ResponseHelper::success(null, 'Payment method deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $data = $this->paymentService->restoreCustomerPaymentMethod($id);

        return ResponseHelper::success($data, 'Payment method restored successfully');
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $this->paymentService->forceDeleteCustomerPaymentMethod($id);

        return ResponseHelper::success(null, 'Payment method permanently deleted');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->paymentService->toggleStatus($id);

        return ResponseHelper::success($data, 'Status updated successfully');
    }
}
