<?php

namespace App\Services\Production;

use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use App\Models\ProductionStageLog;
use App\Models\ProductionStage;
use App\Models\ProductionConsumption;
use App\Models\ProductionOutput;
use App\Models\ProductionCost;
use App\Models\ProductionSetting;
use App\Models\BillOfMaterial;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\ProductVariationStock;
use App\Services\ProductService;
use App\Exceptions\ApiException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductionOrderService
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function getAll(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = ProductionOrder::with([
                'product.brand',
                'productVariation',
                'billOfMaterial',
                'rawMaterialWarehouse',
                'finishedGoodsWarehouse',
                'workCenter',
                'currentStage',
                'costSummary',
                'creator'
            ]);

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['priority'])) {
                $query->where('priority', $filters['priority']);
            }

            if (!empty($filters['product_id'])) {
                $query->where('product_id', $filters['product_id']);
            }

            if (!empty($filters['warehouse_id'])) {
                $wh = $filters['warehouse_id'];
                $query->where(function ($q) use ($wh) {
                    $q->where('raw_material_warehouse_id', $wh)
                      ->orWhere('finished_goods_warehouse_id', $wh);
                });
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($pq) use ($search) {
                            $pq->where('title', 'like', "%{$search}%");
                        });
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching production orders: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch production orders');
        }
    }

    public function getById(int $id): ProductionOrder
    {
        $order = ProductionOrder::with([
            'productionPlan',
            'product.brand',
            'productVariation',
            'billOfMaterial.items.product',
            'rawMaterialWarehouse',
            'finishedGoodsWarehouse',
            'workCenter',
            'currentStage',
            'items.product.brand',
            'items.productVariation',
            'stageLogs.stage.workCenter',
            'stageLogs.operator',
            'consumptions.product',
            'consumptions.productVariation',
            'consumptions.warehouse',
            'outputs.product',
            'outputs.warehouse',
            'wastages.product',
            'wastages.warehouse',
            'qualityChecks.inspector',
            'qualityChecks.defectiveWarehouse',
            'costSummary',
            'creator'
        ])->find($id);

        if (!$order) {
            throw ApiException::notFound('Production Order');
        }

        return $order;
    }

    /**
     * Check raw material availability in the specified warehouse
     */
    public function checkStockAvailability(int $bomId, float $productionQty, int $warehouseId): array
    {
        $bom = BillOfMaterial::with(['items.product', 'items.productVariation'])->find($bomId);
        if (!$bom) {
            throw ApiException::notFound('Bill of Material');
        }

        $bomBaseQty = (float)$bom->production_quantity ?: 1.0;
        $multiplier = $productionQty / $bomBaseQty;

        $itemsStatus = [];
        $hasShortage = false;

        foreach ($bom->items as $item) {
            $requiredQty = (float)$item->quantity * $multiplier * (1 + ((float)$item->wastage_percentage / 100));
            $availableStock = $this->getAvailableStock($item->product_id, $item->product_variation_id, $warehouseId);
            $shortage = max(0, $requiredQty - $availableStock);

            if ($shortage > 0) {
                $hasShortage = true;
            }

            $itemsStatus[] = [
                'product_id' => $item->product_id,
                'product_name' => $item->product?->title ?? 'Unknown Product',
                'product_variation_id' => $item->product_variation_id,
                'unit' => $item->unit,
                'required_quantity' => round($requiredQty, 4),
                'available_quantity' => round($availableStock, 4),
                'shortage_quantity' => round($shortage, 4),
                'unit_cost' => (float)$item->unit_cost,
                'is_sufficient' => $shortage == 0,
            ];
        }

        return [
            'is_sufficient' => !$hasShortage,
            'items' => $itemsStatus,
        ];
    }

    public function create(array $data): ProductionOrder
    {
        DB::beginTransaction();
        try {
            if (empty($data['order_number'])) {
                $data['order_number'] = $this->generateOrderNumber();
            }
            $data['created_by'] = Auth::id();

            $bom = BillOfMaterial::with(['items'])->findOrFail($data['bill_of_material_id']);
            $plannedQty = (float)($data['planned_quantity'] ?? 1);
            $bomBaseQty = (float)$bom->production_quantity ?: 1.0;
            $multiplier = $plannedQty / $bomBaseQty;

            // Estimated costs
            $estMaterialCost = (float)$bom->estimated_material_cost * $multiplier;
            $estOverhead = ((float)$bom->labor_cost +
                (float)$bom->machine_cost +
                (float)$bom->electricity_cost +
                (float)$bom->overhead_cost +
                (float)$bom->packaging_cost +
                (float)$bom->other_cost) * $multiplier;

            $data['estimated_total_cost'] = $estMaterialCost + $estOverhead;

            $order = ProductionOrder::create($data);

            // Create Order Item Snapshots
            foreach ($bom->items as $item) {
                $reqQty = (float)$item->quantity * $multiplier * (1 + ((float)$item->wastage_percentage / 100));
                $unitCost = (float)$item->unit_cost;
                $totCost = round($reqQty * $unitCost, 2);

                ProductionOrderItem::create([
                    'company_id' => $order->company_id,
                    'production_order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_variation_id' => $item->product_variation_id,
                    'required_quantity' => $reqQty,
                    'consumed_quantity' => 0,
                    'wastage_quantity' => 0,
                    'unit' => $item->unit,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totCost,
                    'status' => 'pending',
                ]);
            }

            // Initialize stages
            $stages = ProductionStage::where('status', 'active')->orderBy('sequence', 'asc')->get();
            foreach ($stages as $index => $stage) {
                ProductionStageLog::create([
                    'company_id' => $order->company_id,
                    'production_order_id' => $order->id,
                    'production_stage_id' => $stage->id,
                    'status' => $index === 0 ? 'pending' : 'pending',
                ]);
            }

            if ($stages->isNotEmpty()) {
                $order->update(['current_stage_id' => $stages->first()->id]);
            }

            // Create Initial Cost Record
            ProductionCost::create([
                'company_id' => $order->company_id,
                'production_order_id' => $order->id,
                'raw_material_cost' => 0,
                'labor_cost' => (float)$bom->labor_cost * $multiplier,
                'machine_cost' => (float)$bom->machine_cost * $multiplier,
                'electricity_cost' => (float)$bom->electricity_cost * $multiplier,
                'overhead_cost' => (float)$bom->overhead_cost * $multiplier,
                'packaging_cost' => (float)$bom->packaging_cost * $multiplier,
                'wastage_cost' => 0,
                'other_cost' => (float)$bom->other_cost * $multiplier,
                'total_cost' => $data['estimated_total_cost'],
                'produced_quantity' => 0,
                'cost_per_unit' => $plannedQty > 0 ? round($data['estimated_total_cost'] / $plannedQty, 4) : 0,
                'estimated_cost' => $data['estimated_total_cost'],
                'actual_cost' => 0,
                'cost_variance' => 0,
            ]);

            DB::commit();
            return $this->getById($order->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating production order: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create production order: ' . $e->getMessage());
        }
    }

    public function update(int $id, array $data): ProductionOrder
    {
        $order = $this->getById($id);
        if (in_array($order->status, ['completed', 'cancelled'])) {
            throw ApiException::badRequest('Cannot edit an order that is already ' . $order->status);
        }

        try {
            $order->update($data);
            return $this->getById($id);
        } catch (\Exception $e) {
            Log::error('Error updating production order: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update production order');
        }
    }

    /**
     * Start production: checks stock, auto consumes raw materials, updates stages
     */
    public function startProduction(int $id, array $options = []): ProductionOrder
    {
        DB::beginTransaction();
        try {
            $order = $this->getById($id);

            if (!in_array($order->status, ['draft', 'planned', 'paused'])) {
                throw ApiException::badRequest('Only draft, planned, or paused orders can be started');
            }

            // Check stock availability
            $stockCheck = $this->checkStockAvailability(
                $order->bill_of_material_id,
                (float)$order->planned_quantity,
                (int)$order->raw_material_warehouse_id
            );

            if (!$stockCheck['is_sufficient'] && !$order->allow_partial_production && empty($options['force_start'])) {
                throw new ApiException('Insufficient Raw Material Stock', 422, [
                    'stock_check' => $stockCheck
                ]);
            }

            $order->update([
                'status' => 'in_progress',
                'actual_start_date' => $order->actual_start_date ?: now(),
            ]);

            // Deduct raw materials if auto_consume is enabled
            $setting = ProductionSetting::first();
            $autoConsume = $options['auto_consume'] ?? ($setting->auto_consume_on_start ?? true);

            if ($autoConsume && $order->consumptions->isEmpty()) {
                $this->consumeOrderMaterials($order);
            }

            // Update first stage log to in_progress
            $firstStageLog = $order->stageLogs->first();
            if ($firstStageLog && $firstStageLog->status === 'pending') {
                $firstStageLog->update([
                    'status' => 'in_progress',
                    'started_at' => now(),
                    'operator_id' => Auth::id(),
                ]);
            }

            DB::commit();
            return $this->getById($id);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error starting production: ' . $e->getMessage());
            throw ApiException::serverError('Failed to start production: ' . $e->getMessage());
        }
    }

    /**
     * Progress order through production stages
     */
    public function progressStage(int $id, int $stageId, array $data = []): ProductionOrder
    {
        DB::beginTransaction();
        try {
            $order = $this->getById($id);

            $currentLog = ProductionStageLog::where('production_order_id', $order->id)
                ->where('production_stage_id', $order->current_stage_id)
                ->first();

            if ($currentLog && $currentLog->status === 'in_progress') {
                $started = $currentLog->started_at ?: now();
                $duration = now()->diffInMinutes($started);
                $currentLog->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'duration_minutes' => $duration,
                    'notes' => $data['notes'] ?? null,
                ]);
            }

            $nextLog = ProductionStageLog::where('production_order_id', $order->id)
                ->where('production_stage_id', $stageId)
                ->first();

            if ($nextLog) {
                $nextLog->update([
                    'status' => 'in_progress',
                    'started_at' => now(),
                    'operator_id' => Auth::id(),
                ]);
            }

            $order->update(['current_stage_id' => $stageId]);

            DB::commit();
            return $this->getById($id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error progressing production stage: ' . $e->getMessage());
            throw ApiException::serverError('Failed to progress production stage');
        }
    }

    public function pauseProduction(int $id, ?string $reason = null): ProductionOrder
    {
        $order = $this->getById($id);
        if ($order->status !== 'in_progress') {
            throw ApiException::badRequest('Only in-progress orders can be paused');
        }

        $order->update([
            'status' => 'paused',
            'notes' => $reason ? ($order->notes . "\nPaused reason: " . $reason) : $order->notes,
        ]);

        return $this->getById($id);
    }

    public function resumeProduction(int $id): ProductionOrder
    {
        $order = $this->getById($id);
        if ($order->status !== 'paused') {
            throw ApiException::badRequest('Only paused orders can be resumed');
        }

        $order->update(['status' => 'in_progress']);
        return $this->getById($id);
    }

    /**
     * Complete production: adds finished product stock and finalizes cost ledger
     */
    public function completeProduction(int $id, array $data = []): ProductionOrder
    {
        DB::beginTransaction();
        try {
            $order = $this->getById($id);

            if (in_array($order->status, ['completed', 'cancelled'])) {
                throw ApiException::badRequest('Order is already ' . $order->status);
            }

            $producedQty = (float)($data['produced_quantity'] ?? $order->planned_quantity);
            $rejectedQty = (float)($data['rejected_quantity'] ?? 0);
            $batchNumber = $data['batch_number'] ?? ('BATCH-' . $order->order_number);

            // Increase finished goods stock
            $product = Product::findOrFail($order->product_id);
            if ($product->manage_stock) {
                $this->productService->adjustStock($product->id, [
                    'warehouse_id' => $order->finished_goods_warehouse_id,
                    'variation_id' => $order->product_variation_id,
                    'quantity' => (int)round($producedQty),
                    'batch_number' => $batchNumber,
                    'transaction_type' => 'adjustment',
                    'reference_type' => 'ProductionOutput',
                    'reference_id' => $order->id,
                    'notes' => "Production Output for Order: {$order->order_number}"
                ]);
            }

            // Create Production Output record
            $unitCost = $order->costSummary?->cost_per_unit ?: round((float)$order->estimated_total_cost / max(1, $producedQty), 4);
            ProductionOutput::create([
                'company_id' => $order->company_id,
                'production_order_id' => $order->id,
                'product_id' => $order->product_id,
                'product_variation_id' => $order->product_variation_id,
                'warehouse_id' => $order->finished_goods_warehouse_id,
                'batch_number' => $batchNumber,
                'quantity' => $producedQty,
                'unit' => $order->unit,
                'unit_cost' => $unitCost,
                'total_cost' => round($producedQty * $unitCost, 2),
                'output_at' => now(),
                'created_by' => Auth::id(),
                'notes' => $data['notes'] ?? null,
            ]);

            // Finalize all remaining stage logs
            ProductionStageLog::where('production_order_id', $order->id)
                ->where('status', '!=', 'completed')
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

            // Compute Final Actual Costs
            $rawMaterialCost = ProductionConsumption::where('production_order_id', $order->id)->sum('total_cost');
            $wastageCost = \App\Models\ProductionWastage::where('production_order_id', $order->id)->sum('total_cost');
            
            $cost = $order->costSummary;
            $overhead = $cost ? (
                (float)$cost->labor_cost +
                (float)$cost->machine_cost +
                (float)$cost->electricity_cost +
                (float)$cost->overhead_cost +
                (float)$cost->packaging_cost +
                (float)$cost->other_cost
            ) : 0;

            $actualTotal = $rawMaterialCost + $wastageCost + $overhead;
            $costPerUnit = $producedQty > 0 ? round($actualTotal / $producedQty, 4) : 0;
            $variance = $actualTotal - (float)$order->estimated_total_cost;

            if ($cost) {
                $cost->update([
                    'raw_material_cost' => $rawMaterialCost,
                    'wastage_cost' => $wastageCost,
                    'total_cost' => $actualTotal,
                    'produced_quantity' => $producedQty,
                    'cost_per_unit' => $costPerUnit,
                    'actual_cost' => $actualTotal,
                    'cost_variance' => $variance,
                ]);
            }

            $order->update([
                'status' => 'completed',
                'produced_quantity' => $producedQty,
                'rejected_quantity' => $rejectedQty,
                'actual_completion_date' => now(),
                'actual_total_cost' => $actualTotal,
            ]);

            DB::commit();
            return $this->getById($id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error completing production: ' . $e->getMessage());
            throw ApiException::serverError('Failed to complete production: ' . $e->getMessage());
        }
    }

    /**
     * Cancel production: reverses any consumed raw materials
     */
    public function cancelProduction(int $id, ?string $reason = null): ProductionOrder
    {
        DB::beginTransaction();
        try {
            $order = $this->getById($id);
            if ($order->status === 'completed') {
                throw ApiException::badRequest('Completed production orders cannot be cancelled');
            }

            // Reverse consumed materials
            foreach ($order->consumptions as $consumption) {
                $product = Product::find($consumption->product_id);
                if ($product && $product->manage_stock) {
                    $this->productService->adjustStock($product->id, [
                        'warehouse_id' => $consumption->warehouse_id,
                        'variation_id' => $consumption->product_variation_id,
                        'quantity' => (int)round((float)$consumption->quantity), // Positive to restore
                        'transaction_type' => 'adjustment',
                        'reference_type' => 'ProductionReturn',
                        'reference_id' => $order->id,
                        'notes' => "Reversal of consumption for cancelled PO: {$order->order_number}"
                    ]);
                }
            }

            $order->update([
                'status' => 'cancelled',
                'notes' => $reason ? ($order->notes . "\nCancellation reason: " . $reason) : $order->notes,
            ]);

            DB::commit();
            return $this->getById($id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling production: ' . $e->getMessage());
            throw ApiException::serverError('Failed to cancel production: ' . $e->getMessage());
        }
    }

    /**
     * Consume raw materials from inventory for an order
     */
    public function consumeOrderMaterials(ProductionOrder $order): void
    {
        foreach ($order->items as $item) {
            $qtyToDeduct = (float)$item->required_quantity;
            $unitCost = (float)$item->unit_cost;
            $totalCost = round($qtyToDeduct * $unitCost, 2);

            $product = Product::find($item->product_id);
            if ($product && $product->manage_stock) {
                $this->productService->adjustStock($product->id, [
                    'warehouse_id' => $order->raw_material_warehouse_id,
                    'variation_id' => $item->product_variation_id,
                    'quantity' => -(int)round($qtyToDeduct), // Negative to deduct
                    'transaction_type' => 'adjustment',
                    'reference_type' => 'ProductionConsumption',
                    'reference_id' => $order->id,
                    'notes' => "Production Consumption for PO: {$order->order_number}"
                ]);
            }

            ProductionConsumption::create([
                'company_id' => $order->company_id,
                'production_order_id' => $order->id,
                'product_id' => $item->product_id,
                'product_variation_id' => $item->product_variation_id,
                'warehouse_id' => $order->raw_material_warehouse_id,
                'quantity' => $qtyToDeduct,
                'unit' => $item->unit,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'consumed_at' => now(),
                'created_by' => Auth::id(),
            ]);

            $item->update([
                'consumed_quantity' => $qtyToDeduct,
                'status' => 'fully_consumed',
            ]);
        }
    }

    private function getAvailableStock(int $productId, ?int $variationId, int $warehouseId): float
    {
        if ($variationId) {
            $stock = ProductVariationStock::where('product_variation_id', $variationId)
                ->where('warehouse_id', $warehouseId)
                ->value('quantity');
            return (float)($stock ?? 0);
        }

        $product = Product::find($productId);
        if (!$product) return 0.0;

        if (!empty($product->warehouse_info) && is_array($product->warehouse_info)) {
            foreach ($product->warehouse_info as $wh) {
                if ((string)($wh['warehouse_id'] ?? '') === (string)$warehouseId) {
                    return (float)($wh['quantity'] ?? 0);
                }
            }
        }

        return (float)($product->available_stock ?? $product->stock_quantity ?? 0);
    }

    private function generateOrderNumber(): string
    {
        $prefix = 'PO';
        $setting = ProductionSetting::first();
        if ($setting && !empty($setting->order_prefix)) {
            $prefix = $setting->order_prefix;
        }

        $date = now()->format('Ymd');
        $count = ProductionOrder::whereDate('created_at', now())->count() + 1;
        return sprintf('%s-%s-%04d', $prefix, $date, $count);
    }
}
