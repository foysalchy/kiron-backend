<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\{AddPurchasePaymentRequest, StorePurchaseRequest, UpdatePurchaseRequest};
use App\Services\PurchaseService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\{JsonResponse, Request};

class PurchaseController extends Controller
{
    public function __construct(
        protected PurchaseService $purchaseService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'warehouse_id' => $request->query('warehouse_id'),
            'supplier_id' => $request->query('supplier_id'),
            'status' => $request->query('status'),
            'payment_status' => $request->query('payment_status'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'purchase_date'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->purchaseService->getAllPurchases($filters, true);

        return ResponseHelper::success($data, 'Purchases retrieved successfully');
    }

    public function store(StorePurchaseRequest $request): JsonResponse
    {
        $data = $this->purchaseService->createPurchase($request->validated());

        return ResponseHelper::success($data, 'Purchase created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->purchaseService->getPurchaseById($id);

        return ResponseHelper::success($data, 'Purchase retrieved successfully');
    }

    public function update(UpdatePurchaseRequest $request, int $id): JsonResponse
    {
        $data = $this->purchaseService->updatePurchase($id, $request->validated());

        return ResponseHelper::success($data, 'Purchase updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->purchaseService->deletePurchase($id);

        return ResponseHelper::success(null, 'Purchase deleted successfully');
    }

    public function changeStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:0,1,2',
        ]);

        $data = $this->purchaseService->changeStatus($id, $request->status);

        return ResponseHelper::success($data, 'Purchase status updated successfully');
    }

    public function addPayment(AddPurchasePaymentRequest $request, int $id): JsonResponse
    {


        $data = $this->purchaseService->addPayment($id, $request->validated());

        return ResponseHelper::success($data, 'Payment added successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $data = $this->purchaseService->restorePurchase($id);

        return ResponseHelper::success($data, 'Purchase restored successfully');
    }

    /**
     * Permanently delete a Purchase
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->purchaseService->forceDeletePurchase($id);

        return ResponseHelper::success(null, 'Purchase permanently deleted successfully');
    }
}
