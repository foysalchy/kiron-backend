<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\{StorePurchaseReturnRequest, UpdatePurchaseReturnRequest};
use App\Services\PurchaseReturnService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Validation\Rule;

class PurchaseReturnController extends Controller
{
    public function __construct(
        protected PurchaseReturnService $purchaseReturnService
    ) {}

    /**
     * Get all purchase returns
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'purchase_id' => $request->query('purchase_id'),
            'supplier_id' => $request->query('supplier_id'),
            'status' => $request->query('status'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'return_date'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $columns = ['*'];
        if ($request->has('select')) {
            $select = $request->query('select');
            $columns = is_string($select) ? explode(',', $select) : $select;
        }

        if ($request->has('with')) {
            $with = is_string($request->query('with')) ? explode(',', $request->query('with')) : $request->query('with');
            if (empty($with) || $with[0] === '') $with = [];
            $filters['with'] = $with;
        }

        $data = $this->purchaseReturnService->getAllPurchaseReturns($filters, true, $columns);

        return ResponseHelper::success($data, 'Purchase returns retrieved successfully');
    }

    /**
     * Create new purchase return
     */
    public function store(StorePurchaseReturnRequest $request): JsonResponse
    {
        $data = $this->purchaseReturnService->createPurchaseReturn($request->validated());

        return ResponseHelper::success($data, 'Purchase return created successfully', 201);
    }

    /**
     * Get single purchase return
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->purchaseReturnService->getPurchaseReturnById($id);

        return ResponseHelper::success($data, 'Purchase return retrieved successfully');
    }

    /**
     * Update purchase return
     */
    public function update(UpdatePurchaseReturnRequest $request, int $id): JsonResponse
    {
        $data = $this->purchaseReturnService->updatePurchaseReturn($id, $request->validated());

        return ResponseHelper::success($data, 'Purchase return updated successfully');
    }
    /**
     * Add payment to return
     */
    public function addPayment(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,card,bank,mobile_banking,cheque',
            'reference_no' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $data = $this->purchaseReturnService->addPayment($id, $request->only([
            'amount',
            'payment_method',
            'reference_no',
            'note'
        ]));

        return ResponseHelper::success($data, 'Payment added successfully');
    }

    /**
     * Delete purchase return
     */
    public function purchaseProducts(int $purchaseId): JsonResponse
    {
        $data=$this->purchaseReturnService->purchaseProducts($purchaseId);

        return ResponseHelper::success($data, 'Purchase products successfully');
    }
    /**
     * Delete purchase return
     */
    public function destroy(int $id): JsonResponse
    {
        $this->purchaseReturnService->deletePurchaseReturn($id);

        return ResponseHelper::success(null, 'Purchase return deleted successfully');
    }

    /**
     * Change status
     */
    public function changeStatus(Request $request, int $id): JsonResponse
    {
       $request->validate([
            'status' => [
                'required',
                Rule::in([
                    Status::Draft->value,     
                    Status::Completed->value,  
                    Status::Waiting->value,  
                    Status::Cancelled->value,  
                    Status::NotCleared->value, 
                    Status::Cleared->value,  
                ]),
            ],
        ]);
        $data = $this->purchaseReturnService->changeStatus($id, $request->status);

        return ResponseHelper::success($data, 'Purchase return status updated successfully');
    }

    /**
     * Restore purchase return
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->purchaseReturnService->restorePurchaseReturn($id);

        return ResponseHelper::success($data, 'Purchase return restored successfully');
    }

    /**
     * Force delete purchase return
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->purchaseReturnService->forceDeletePurchaseReturn($id);

        return ResponseHelper::success(null, 'Purchase return permanently deleted successfully');
    }
}
