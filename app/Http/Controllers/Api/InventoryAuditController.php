<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InventoryAuditService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\{JsonResponse, Request};

class InventoryAuditController extends Controller
{
    public function __construct(
        protected InventoryAuditService $inventoryAuditService
    ) {}

    /**
     * Get all inventory audits
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'warehouse_id' => $request->query('warehouse_id'),
                'audit_type' => $request->query('audit_type'),
                'status' => $request->query('status'),
                'date_from' => $request->query('date_from'),
                'date_to' => $request->query('date_to'),
                'search' => $request->query('search'),
                'sort_by' => $request->query('sort_by'),
                'sort_order' => $request->query('sort_order'),
                'per_page' => $request->query('per_page'),
            ];

            $audits = $this->inventoryAuditService->getAllAudits($filters);

            return ResponseHelper::success($audits, 'Inventory audits retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get single inventory audit
     */
    public function show(int $id): JsonResponse
    {
        try {
            $audit = $this->inventoryAuditService->getAuditById($id);

            return ResponseHelper::success($audit, 'Inventory audit retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Create inventory audit
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'audit_date' => 'required|date',
            'audit_type' => 'required|in:full,partial,cycle',
            'notes' => 'nullable|string',
        ]);

        try {
            $audit = $this->inventoryAuditService->createAudit($validated);

            return ResponseHelper::success($audit, 'Inventory audit created successfully', 201);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Update inventory audit
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'audit_date' => 'sometimes|date',
            'audit_type' => 'sometimes|in:full,partial,cycle',
            'notes' => 'nullable|string',
        ]);

        try {
            $audit = $this->inventoryAuditService->updateAudit($id, $validated);

            return ResponseHelper::success($audit, 'Inventory audit updated successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Start audit (generate items and set to In Progress)
     */
    public function start(int $id): JsonResponse
    {
        try {
            $audit = $this->inventoryAuditService->startAudit($id);

            return ResponseHelper::success($audit, 'Inventory audit started successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Update item count
     */
    public function updateItemCount(Request $request, int $auditId, int $itemId): JsonResponse
    {
        $validated = $request->validate([
            'actual_quantity' => 'required|integer|min:0',
        ]);

        try {
            $audit = $this->inventoryAuditService->updateItemCount(
                $auditId,
                $itemId,
                $validated['actual_quantity']
            );

            return ResponseHelper::success($audit, 'Item count updated successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Complete audit
     */
    public function complete(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'apply_adjustments' => 'sometimes|boolean',
        ]);

        try {
            $audit = $this->inventoryAuditService->completeAudit(
                $id,
                $validated['apply_adjustments'] ?? false
            );

            return ResponseHelper::success($audit, 'Inventory audit completed successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Cancel audit
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $audit = $this->inventoryAuditService->cancelAudit($id);

            return ResponseHelper::success($audit, 'Inventory audit cancelled successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Delete audit
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->inventoryAuditService->deleteAudit($id);

            return ResponseHelper::success(null, 'Inventory audit deleted successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}