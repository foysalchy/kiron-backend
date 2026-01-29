<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentMethodTypeRequest;
use App\Http\Requests\UpdatePaymentMethodTypeRequest;
use App\Services\PaymentMethodTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentMethodTypeController extends Controller
{
    public function __construct(
        protected PaymentMethodTypeService $paymentMethodTypeService
    ) {}
    /**
     * Get all payment method types with optional filters
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

        $data = $this->paymentMethodTypeService->getAllPaymentMethodTypes($filters, true);

        return ResponseHelper::success($data, 'Payment method types retrieved successfully');
    }

    /**
     * Store a new payment method type
     */
    public function store(StorePaymentMethodTypeRequest $request): JsonResponse
    {
        $type = $this->paymentMethodTypeService->createMethodType($request->validated());

        return ResponseHelper::created($type, 'Payment method type created successfully');
    }

    /**
     * Get payment method type by ID
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->paymentMethodTypeService->getMethodTypeById($id);

        return ResponseHelper::success($data, 'Payment method type retrieved successfully');
    }

    /**
     * Update payment method type
     */
    public function update(UpdatePaymentMethodTypeRequest $request, int $id): JsonResponse
    {
        $data = $this->paymentMethodTypeService->updatePaymentMethodType($id, $request->validated());

        return ResponseHelper::success($data, 'Payment method type updated successfully');
    }

    /**
     * Soft delete payment method type
     */
    public function destroy(int $id): JsonResponse
    {
        $this->paymentMethodTypeService->deletePaymentMethodType($id);

        return ResponseHelper::success(null, 'Payment method type deleted successfully');
    }

    /**
     * Restore soft deleted payment method type
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->paymentMethodTypeService->restorePaymentMethodType($id);

        return ResponseHelper::success($data, 'Payment method type restored successfully');
    }

    /**
     * Permanently delete payment method type
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->paymentMethodTypeService->forceDeletePaymentMethodType($id);

        return ResponseHelper::success(null, 'Payment method type permanently deleted');
    }
}
