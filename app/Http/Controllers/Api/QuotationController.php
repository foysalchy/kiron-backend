<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\{StoreQuotationRequest, UpdateQuotationRequest};
use App\Services\QuotationService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Validation\Rule;

class QuotationController extends Controller
{
    public function __construct(
        protected QuotationService $quotationService
    ) {}

    /**
     * Get all quotations
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'warehouse_id' => $request->query('warehouse_id'),
            'customer_id' => $request->query('customer_id'),
            'status' => $request->query('status'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'search' => $request->query('search'),
            'not_converted' => $request->query('not_converted'),
            'sort_by' => $request->query('sort_by', 'id'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->quotationService->getAllQuotations($filters, true);

        return ResponseHelper::success($data, 'Quotations retrieved successfully');
    }

    /**
     * Create new quotation
     */
    public function store(StoreQuotationRequest $request): JsonResponse
    {
        $data = $this->quotationService->createQuotation($request->validated());

        return ResponseHelper::success($data, 'Quotation created successfully', 201);
    }

    /**
     * Get quotation by ID
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->quotationService->getQuotationById($id);

        return ResponseHelper::success($data, 'Quotation retrieved successfully');
    }

    /**
     * Update quotation
     */
    public function update(UpdateQuotationRequest $request, int $id): JsonResponse
    {
        $data = $this->quotationService->updateQuotation($id, $request->validated());

        return ResponseHelper::success($data, 'Quotation updated successfully');
    }

    /**
     * Delete quotation
     */
    public function destroy(int $id): JsonResponse
    {
        $this->quotationService->deleteQuotation($id);

        return ResponseHelper::success(null, 'Quotation deleted successfully');
    }

    /**
     * Change quotation status
     */
    public function changeStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required'],
        ]);

        $data = $this->quotationService->changeStatus($id, $request->status);

        return ResponseHelper::success($data, 'Quotation status updated successfully');
    }

    /**
     * Convert quotation to order
     */
    public function convertToOrder(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'order_date' => ['nullable', 'date'],
            'reference_no' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'in:pos,sales'],
            'status' => ['nullable', 'integer'],
            'note' => ['nullable', 'string'],
            'payments' => ['nullable', 'array'],
            'payments.*.amount' => ['required', 'numeric', 'min:0'],
            'payments.*.payment_method' => ['required', 'string'],
            'payments.*.reference_no' => ['nullable', 'string'],
            'payments.*.note' => ['nullable', 'string'],
        ]);

        $data = $this->quotationService->convertToOrder($id, $validated);

        return ResponseHelper::success($data, 'Quotation converted to order successfully');
    }

    /**
     * Restore deleted quotation
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->quotationService->restoreQuotation($id);

        return ResponseHelper::success($data, 'Quotation restored successfully');
    }

    /**
     * Permanently delete quotation
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->quotationService->forceDeleteQuotation($id);

        return ResponseHelper::success(null, 'Quotation permanently deleted successfully');
    }
    /**
     * Bulk update status for quotations
     */
    public function bulkStatus(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['exists:quotations,id'],
            'status' => ['required'],
        ]);

        // ১. সার্ভিসের রিটার্ন করা ডাটা ভেরিয়েবলে রাখুন
        $data = $this->quotationService->bulkUpdateStatus($request->ids, (int)$request->status);

        // ২. null এর জায়গায় $data পাঠিয়ে দিন
        return ResponseHelper::success($data, 'Quotations status updated successfully');
    }

    /**
     * Bulk delete quotations
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['exists:quotations,id'],
        ]);

        $this->quotationService->bulkDelete($request->ids);

        return ResponseHelper::success(null, 'Quotations deleted successfully');
    }
}
