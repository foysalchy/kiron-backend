<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\{InventoryAudit, Product, ProductVariation, ProductStockLedger, ProductVariationStockLedger};
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{Auth, DB, Log, Schema};

class InventoryAuditService
{
    protected StockAdjustmentService $stockAdjustmentService;


    public function __construct(
        StockAdjustmentService $stockAdjustmentService
    ) {
        $this->stockAdjustmentService = $stockAdjustmentService;
    }
    private function getVariationColumnName(): string
    {
        if (Schema::hasColumn('product_variation_stocks', 'product_variation_id')) {
            return 'product_variation_id';
        }
        return 'variation_id';
    }

    /**
     * Get all inventory audits with filters
     */
    public function getAllAudits(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = InventoryAudit::with([
                'warehouse',
                'creator',
                'items.product.brand',
                'items.variation.attributes.attributeGroup',
                'items.variation.attributes.attributeValue',
            ]);

            if (isset($filters['warehouse_id'])) {
                $query->where('warehouse_id', $filters['warehouse_id']);
            }

            if (isset($filters['audit_type'])) {
                $query->where('audit_type', $filters['audit_type']);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['date_from'])) {
                $query->whereDate('audit_date', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->whereDate('audit_date', '<=', $filters['date_to']);
            }

            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('audit_number', 'like', "%{$filters['search']}%")
                        ->orWhere('notes', 'like', "%{$filters['search']}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'audit_date';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            $results = $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();

            // Add calculated stats to each audit
            $this->addCalculatedStats($results);

            return $results;
        } catch (\Exception $e) {
            Log::error('Error fetching inventory audits: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch inventory audits');
        }
    }

    /**
     * Get audit by ID (Used for returning API response)
     */
    public function getAuditById(int $id): InventoryAudit
    {
        $audit = InventoryAudit::with([
            'warehouse',
            'creator',
            'items.product.brand',
            'items.variation.attributes.attributeGroup',
            'items.variation.attributes.attributeValue',
        ])->find($id);

        if (!$audit) {
            throw ApiException::notFound('Inventory Audit');
        }

        // Add calculated stats
        $this->addCalculatedStats(collect([$audit]));

        return $audit;
    }

    /**
     * Create inventory audit
     */
    public function createAudit(array $data): InventoryAudit
    {
        DB::beginTransaction();

        try {
            $data['created_by'] = Auth::id();
            $data['status'] = Status::Pending->value;

            // Lock দিয়ে audit_number generate করা - race condition আটকাতে
            $data['audit_number'] = $this->generateAuditNumber($data['company_id']);

            // Create audit
            $audit = InventoryAudit::create($data);

            // If audit_type is 'full', automatically generate items from warehouse
            if ($data['audit_type'] === 'full') {
                $this->generateAuditItems($audit);
            }

            DB::commit();

            Log::info('Inventory audit created', [
                'audit_id' => $audit->id,
                'audit_number' => $audit->audit_number
            ]);
            LogHelper::created('inventory_audit', $audit->id, $audit->company_id, $audit->audit_number);

            return $this->getAuditById($audit->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Inventory audit creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create inventory audit: ' . $e->getMessage());
        }
    }

    /**
     * Race-condition safe audit number generator
     */
    private function generateAuditNumber(int $companyId): string
    {
        $lastAudit = InventoryAudit::where('company_id', $companyId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();

        $nextNumber = $lastAudit
            ? (intval(substr($lastAudit->audit_number, -4)) + 1)
            : 1;

        return 'AUD-' . now()->format('Ym') . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Update inventory audit
     */
    public function updateAudit(int $id, array $data): InventoryAudit
    {
        DB::beginTransaction();

        try {
            // FIX: Fetch fresh model without appended properties
            $audit = InventoryAudit::findOrFail($id);

            // Can only update if pending
            if ($audit->status !== Status::Pending->value) {
                throw ApiException::badRequest('Only pending audits can be updated');
            }

            $audit->update($data);

            DB::commit();

            Log::info('Inventory audit updated', ['audit_id' => $id]);
            LogHelper::updated('inventory_audit', $id, $audit->company_id, $audit->audit_number);

            return $this->getAuditById($id);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Inventory audit update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update inventory audit');
        }
    }

    /**
     * Start audit (change status to In Progress and generate items)
     */
    public function startAudit(int $id): InventoryAudit
    {
        DB::beginTransaction();

        try {
            // FIX: Fetch fresh model without appended properties
            $audit = InventoryAudit::findOrFail($id);

            if ($audit->status !== Status::Pending->value) {
                throw ApiException::badRequest('Only pending audits can be started');
            }

            // Generate audit items based on current warehouse stock
            $this->generateAuditItems($audit);

            // Update status (Make sure Status Enum returns correct value for processing)
            $audit->update(['status' => Status::Processing->value]);

            DB::commit();

            Log::info('Inventory audit started', ['audit_id' => $id]);
            LogHelper::custom('started', 'inventory_audit', $id, $audit->company_id, $audit->audit_number);

            return $this->getAuditById($id);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Inventory audit start failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to start inventory audit');
        }
    }

    /**
     * Update item count
     */
    public function updateItemCount(int $auditId, int $itemId, int $actualQuantity): InventoryAudit
    {
        DB::beginTransaction();

        try {
            $audit = InventoryAudit::findOrFail($auditId);

            if ($audit->status !== Status::Processing->value) {
                throw ApiException::badRequest('Can only update counts for audits in progress');
            }

            $item = $audit->items()->findOrFail($itemId);
            $item->update(['actual_quantity' => $actualQuantity]);

            DB::commit();

            Log::info('Audit item count updated', [
                'audit_id' => $auditId,
                'item_id' => $itemId,
                'actual_quantity' => $actualQuantity
            ]);

            return $this->getAuditById($auditId);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Audit item count update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update item count');
        }
    }

    /**
     * Complete audit
     */
    public function completeAudit(int $id, bool $applyAdjustments = false): InventoryAudit
    {
        DB::beginTransaction();

        try {
            $audit = InventoryAudit::findOrFail($id);

            if ($audit->status !== Status::Processing->value) {
                throw ApiException::badRequest('Only in-progress audits can be completed');
            }

            // Check if all items are counted
            $uncountedItems = $audit->items()->whereNull('actual_quantity')->count();
            if ($uncountedItems > 0) {
                throw ApiException::badRequest("Cannot complete audit. {$uncountedItems} items not counted yet.");
            }

            // Update status
            $audit->update(['status' => Status::Completed->value]);

            // Optionally apply stock adjustments
            if ($applyAdjustments) {
                $this->applyStockAdjustments($audit);
            }

            DB::commit();

            Log::info('Inventory audit completed', ['audit_id' => $id]);
            LogHelper::custom('completed', 'inventory_audit', $id, $audit->company_id, $audit->audit_number);

            return $this->getAuditById($id);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Inventory audit completion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to complete inventory audit');
        }
    }

    /**
     * Cancel audit
     */
    public function cancelAudit(int $id): InventoryAudit
    {
        DB::beginTransaction();

        try {
            $audit = InventoryAudit::findOrFail($id);

            if ($audit->status === Status::Completed->value) {
                throw ApiException::badRequest('Cannot cancel completed audits');
            }

            $audit->update(['status' => Status::Cancelled->value]);

            DB::commit();

            Log::info('Inventory audit cancelled', ['audit_id' => $id]);
            LogHelper::custom('cancelled', 'inventory_audit', $id, $audit->company_id, $audit->audit_number);

            return $this->getAuditById($id);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Inventory audit cancellation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to cancel inventory audit');
        }
    }

    /**
     * Delete audit
     */
    public function deleteAudit(int $id): void
    {
        DB::beginTransaction();

        try {
            $audit = InventoryAudit::findOrFail($id);

            if ($audit->status === Status::Completed->value) {
                throw ApiException::badRequest('Cannot delete completed audits');
            }

            $audit->delete();

            DB::commit();

            Log::info('Inventory audit deleted', ['audit_id' => $id]);
            LogHelper::deleted('inventory_audit', $id, $audit->company_id, $audit->audit_number);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Inventory audit deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete inventory audit');
        }
    }

    /**
     * Generate audit items from warehouse stock
     */
    private function generateAuditItems(InventoryAudit $audit): void
    {
        $warehouseId = $audit->warehouse_id;
        $variationColumn = $this->getVariationColumnName();

        // Get single products in this warehouse
        $singleProducts = Product::where('type', 'single')
            ->get()
            ->filter(function ($product) use ($warehouseId) {
                $stock = collect($product->warehouse_info ?? [])
                    ->firstWhere('warehouse_id', (string) $warehouseId);
                return $stock && ($stock['quantity'] ?? 0) > 0;
            });

        foreach ($singleProducts as $product) {
            $stock = collect($product->warehouse_info ?? [])
                ->firstWhere('warehouse_id', (string) $warehouseId);

            $audit->items()->create([
                'product_id' => $product->id,
                'variation_id' => null,
                'expected_quantity' => $stock['quantity'] ?? 0,
                'unit_cost' => $product->regular_price ?? 0,
            ]);
        }

        // Get variation products in this warehouse
        $variationStocks = DB::table('product_variation_stocks')
            ->join('product_variations', "product_variation_stocks.{$variationColumn}", '=', 'product_variations.id')
            ->where('product_variation_stocks.warehouse_id', $warehouseId)
            ->where('product_variation_stocks.quantity', '>', 0)
            ->select(
                'product_variations.id as variation_id',
                'product_variations.product_id',
                'product_variation_stocks.quantity',
                'product_variations.regular_price'
            )
            ->get();

        foreach ($variationStocks as $stock) {
            $audit->items()->create([
                'product_id' => $stock->product_id,
                'variation_id' => $stock->variation_id,
                'expected_quantity' => $stock->quantity,
                'unit_cost' => $stock->regular_price ?? 0,
            ]);
        }
    }

    /**
     * Apply stock adjustments from audit variances
     */
    private function applyStockAdjustments(InventoryAudit $audit): void
    {


        $itemsWithVariance = $audit->items()
            ->whereRaw('actual_quantity != expected_quantity')
            ->get();

        if ($itemsWithVariance->isEmpty()) {
            return;
        }

        $adjustmentItems = $itemsWithVariance->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'variation_id' => $item->variation_id,
                'quantity_to_adjust' => $item->actual_quantity - $item->expected_quantity,
            ];
        })->toArray();



        $this->stockAdjustmentService->createAdjustment([
            'company_id' => $audit->company_id,
            'warehouse_id' => $audit->warehouse_id,
            'adjustment_date' => $audit->audit_date,
            'adjustment_reason' => 'audit_variance',
            'notes' => "Auto-generated from audit: {$audit->audit_number}",
            'items' => $adjustmentItems,
        ]);
    }

    /**
     * Add calculated statistics to audits
     */
    private function addCalculatedStats($results): void
    {
        $collection = $results instanceof LengthAwarePaginator
            ? $results->getCollection()
            : $results;

        $collection->each(function ($audit) {
            $items = $audit->items;

            // Notice we attach properties dynamically
            $audit->total_items = $items->count();
            $audit->counted_items = $items->whereNotNull('actual_quantity')->count();
            $audit->items_with_variance = $items->filter(function ($item) {
                return $item->actual_quantity !== null && $item->actual_quantity != $item->expected_quantity;
            })->count();

            $audit->total_variance_value = $items->sum(function ($item) {
                if ($item->actual_quantity === null) return 0;
                $variance = $item->actual_quantity - $item->expected_quantity;
                return $variance * $item->unit_cost;
            });
        });

        if ($results instanceof LengthAwarePaginator) {
            $results->setCollection($collection);
        }
    }
}
