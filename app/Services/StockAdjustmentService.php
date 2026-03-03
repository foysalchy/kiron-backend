<?php

namespace App\Services;

use App\Models\{Product, ProductVariation, ProductStockLedger, ProductVariationStockLedger, StockAdjustment, StockAdjustmentItem};
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class StockAdjustmentService
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Get all stock adjustments with filters
     * UPDATED: Now includes variation products
     */
    public function getAllAdjustments(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = StockAdjustment::with([
                'warehouse',
                'items.product.brand',
                'items.variation.attributes.attributeGroup',
                'items.variation.attributes.attributeValue',
                'items.bin',
                'creator'
            ]);

            if (isset($filters['warehouse_id'])) {
                $query->where('warehouse_id', $filters['warehouse_id']);
            }

            if (isset($filters['adjustment_reason'])) {
                $query->where('adjustment_reason', $filters['adjustment_reason']);
            }

            if (isset($filters['date_from'])) {
                $query->whereDate('adjustment_date', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->whereDate('adjustment_date', '<=', $filters['date_to']);
            }

            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('adjustment_number', 'like', "%{$filters['search']}%")
                        ->orWhere('notes', 'like', "%{$filters['search']}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'adjustment_date';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching stock adjustments: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch stock adjustments');
        }
    }

    /**
     * Get adjustment by ID
     */
    public function getAdjustmentById(int $id): StockAdjustment
    {
        $adjustment = StockAdjustment::with([
            'warehouse',
            'items.product.brand',
            'items.variation.attributes.attributeGroup',
            'items.variation.attributes.attributeValue',
            'items.bin',
            'creator'
        ])->find($id);

        if (!$adjustment) {
            throw ApiException::notFound('Stock Adjustment');
        }

        return $adjustment;
    }

    /**
     * Create stock adjustment
     * UPDATED: Now supports both single and variation products
     */
    public function createAdjustment(array $data): StockAdjustment
    {
        DB::beginTransaction();

        try {
            $items = $data['items'];
            unset($data['items']);

            // Create adjustment
            $adjustment = StockAdjustment::create($data);

            // Create adjustment items and update stock
            foreach ($items as $item) {
                // Validate stock availability if reducing
                if ($item['quantity_to_adjust'] < 0) {
                    $this->validateStockAvailability(
                        $item['product_id'],
                        $item['variation_id'] ?? null,
                        $data['warehouse_id'],
                        $item['bin_id'] ?? null,
                        $item['batch_number'] ?? null,
                        abs($item['quantity_to_adjust'])
                    );
                }

                // Create adjustment item
                $adjustment->items()->create([
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'bin_id' => $item['bin_id'] ?? null,
                    'batch_number' => $item['batch_number'] ?? null,
                    'serial_numbers' => $item['serial_numbers'] ?? null,
                    'quantity_to_adjust' => $item['quantity_to_adjust'],
                ]);

                // Update product stock using ProductService
                $stockData = [
                    'warehouse_id' => $data['warehouse_id'],
                    'bin_id' => $item['bin_id'] ?? null,
                    'quantity' => $item['quantity_to_adjust'],
                    'transaction_type' => 'adjustment',
                    'reference_type' => 'StockAdjustment',
                    'reference_id' => $adjustment->id,
                    'notes' => "Stock adjustment: {$adjustment->adjustment_number} - Reason: {$data['adjustment_reason']}"
                ];

                // Add variation_id if exists
                if (isset($item['variation_id']) && $item['variation_id']) {
                    $stockData['variation_id'] = $item['variation_id'];
                }

                $this->productService->adjustStock($item['product_id'], $stockData);
            }

            DB::commit();

            Log::info('Stock adjustment created successfully', [
                'adjustment_id' => $adjustment->id,
                'adjustment_number' => $adjustment->adjustment_number
            ]);
            LogHelper::created('stock_adjustment', $adjustment->id, $adjustment->company_id, "Stock adjustment: {$adjustment->adjustment_number} - Reason: {$data['adjustment_reason']}");

            return $this->getAdjustmentById($adjustment->id);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock adjustment creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create stock adjustment: ' . $e->getMessage());
        }
    }

    /**
     * Update stock adjustment
     * UPDATED: Now supports variation products
     */
    public function updateAdjustment(int $id, array $data): StockAdjustment
    {
        DB::beginTransaction();

        try {
            $adjustment = $this->getAdjustmentById($id);

            $items = $data['items'] ?? null;
            unset($data['items']);

            if ($items) {
                // Reverse old adjustments
                $this->reverseAdjustment($adjustment);

                // Delete old items
                $adjustment->items()->delete();

                // Create new items and adjust stock
                foreach ($items as $item) {
                    // Validate stock if reducing
                    if ($item['quantity_to_adjust'] < 0) {
                        $this->validateStockAvailability(
                            $item['product_id'],
                            $item['variation_id'] ?? null,
                            $data['warehouse_id'],
                            $item['bin_id'] ?? null,
                            $item['batch_number'] ?? null,
                            abs($item['quantity_to_adjust'])
                        );
                    }

                    // Create new item
                    $adjustment->items()->create([
                        'product_id' => $item['product_id'],
                        'variation_id' => $item['variation_id'] ?? null,
                        'bin_id' => $item['bin_id'] ?? null,
                        'batch_number' => $item['batch_number'] ?? null,
                        'serial_numbers' => $item['serial_numbers'] ?? null,
                        'quantity_to_adjust' => $item['quantity_to_adjust'],
                    ]);

                    // Adjust stock
                    $stockData = [
                        'warehouse_id' => $data['warehouse_id'],
                        'bin_id' => $item['bin_id'] ?? null,
                        'quantity' => $item['quantity_to_adjust'],
                        'transaction_type' => 'adjustment',
                        'reference_type' => 'StockAdjustment',
                        'reference_id' => $adjustment->id,
                        'notes' => "Stock adjustment: {$adjustment->adjustment_number} - Reason: {$data['adjustment_reason']}"
                    ];

                    if (isset($item['variation_id']) && $item['variation_id']) {
                        $stockData['variation_id'] = $item['variation_id'];
                    }

                    $this->productService->adjustStock($item['product_id'], $stockData);
                }
            }

            $adjustment->update($data);

            DB::commit();

            Log::info('Stock adjustment updated', ['adjustment_id' => $id]);
            LogHelper::updated('stock_adjustment', $id, $adjustment->company_id, "Stock adjustment: {$adjustment->adjustment_number} - Reason: {$data['adjustment_reason']}");

            return $this->getAdjustmentById($adjustment->id);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock adjustment update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update stock adjustment');
        }
    }

    /**
     * Delete stock adjustment
     */
    public function deleteAdjustment(int $id): void
    {
        DB::beginTransaction();

        try {
            $adjustment = $this->getAdjustmentById($id);

            // Reverse the adjustment before deleting
            $this->reverseAdjustment($adjustment);

            $adjustment->delete();

            DB::commit();

            Log::info('Stock adjustment deleted', ['adjustment_id' => $id]);
            LogHelper::deleted('stock_adjustment', $id, $adjustment->company_id, $adjustment->adjustment_number);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock adjustment deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete stock adjustment');
        }
    }

    /**
     * Restore stock adjustment
     */
    public function restoreAdjustment(int $id): StockAdjustment
    {
        DB::beginTransaction();

        try {
            $adjustment = StockAdjustment::withTrashed()->find($id);

            if (!$adjustment) {
                throw ApiException::notFound('Stock Adjustment');
            }

            $adjustment->restore();

            // Re-apply the adjustment
            foreach ($adjustment->items as $item) {
                $stockData = [
                    'warehouse_id' => $adjustment->warehouse_id,
                    'bin_id' => $item->bin_id,
                    'quantity' => $item->quantity_to_adjust,
                    'transaction_type' => 'restoration',
                    'reference_type' => 'StockAdjustment',
                    'reference_id' => $adjustment->id,
                    'notes' => "Restoration of stock adjustment: {$adjustment->adjustment_number}"
                ];

                if ($item->variation_id) {
                    $stockData['variation_id'] = $item->variation_id;
                }

                $this->productService->adjustStock($item->product_id, $stockData);
            }

            DB::commit();

            Log::info('Stock adjustment restored', ['adjustment_id' => $id]);
            LogHelper::custom('restored', 'stock_adjustment', $id, $adjustment->company_id, $adjustment->adjustment_number);

            return $this->getAdjustmentById($adjustment->id);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock adjustment restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore stock adjustment');
        }
    }

    /**
     * Force delete stock adjustment
     * UPDATED: Now deletes both single and variation ledger entries
     */
    public function forceDeleteAdjustment(int $id): void
    {
        DB::beginTransaction();

        try {
            $adjustment = StockAdjustment::withTrashed()->find($id);

            if (!$adjustment) {
                throw ApiException::notFound('Stock Adjustment');
            }

            // Delete items
            StockAdjustmentItem::where('stock_adjustment_id', $id)->forceDelete();

            // Delete related ledger entries (both single and variation)
            ProductStockLedger::where('reference_type', 'StockAdjustment')
                ->where('reference_id', $id)
                ->forceDelete();

            ProductVariationStockLedger::where('reference_type', 'StockAdjustment')
                ->where('reference_id', $id)
                ->forceDelete();

            $adjustment->forceDelete();

            DB::commit();

            Log::info('Stock adjustment force deleted', ['adjustment_id' => $id]);
            LogHelper::custom('force_deleted', 'stock_adjustment', $id, $adjustment->company_id);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock adjustment force deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to force delete stock adjustment');
        }
    }

    /**
     * Reverse stock adjustment
     * UPDATED: Now handles both single and variation products
     */
    private function reverseAdjustment(StockAdjustment $adjustment): void
    {
        foreach ($adjustment->items as $item) {
            // Reverse the adjustment (opposite quantity)
            $stockData = [
                'warehouse_id' => $adjustment->warehouse_id,
                'bin_id' => $item->bin_id,
                'quantity' => -$item->quantity_to_adjust, // Reverse
                'transaction_type' => 'correction',
                'reference_type' => 'StockAdjustment',
                'reference_id' => $adjustment->id,
                'notes' => "Reversal of stock adjustment: {$adjustment->adjustment_number}"
            ];

            if ($item->variation_id) {
                $stockData['variation_id'] = $item->variation_id;
            }

            $this->productService->adjustStock($item->product_id, $stockData);
        }
    }

    /**
     * Validate stock availability
     * UPDATED: Now handles both single and variation products
     */
    private function validateStockAvailability(
        int $productId,
        ?int $variationId,
        int $warehouseId,
        ?int $binId,
        ?string $batchNumber,
        int $requiredQuantity
    ): void {

        if ($variationId) {
            // Validate variation stock
            $availableStock = ProductVariationStockLedger::getCurrentStock(

                $variationId,
                $warehouseId,
                $binId,
                $batchNumber
            );

            if ($availableStock < $requiredQuantity) {
                $product = Product::find($productId);
                $variation = ProductVariation::find($variationId);
                throw ApiException::badRequest(
                    "Insufficient stock for {$product->title} (Variation: {$variation->sku}). Available: {$availableStock}, Required: {$requiredQuantity}"
                );
            }
        } else {
            // Validate single product stock
            $availableStock = ProductStockLedger::getCurrentStock(
                $productId,
                $warehouseId,
                $binId,
                $batchNumber
            );

            if ($availableStock < $requiredQuantity) {
                $product = Product::find($productId);
                throw ApiException::badRequest(
                    "Insufficient stock for product: {$product->title}. Available: {$availableStock}, Required: {$requiredQuantity}"
                );
            }
        }
    }
}
