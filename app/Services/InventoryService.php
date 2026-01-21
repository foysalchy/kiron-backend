<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Company;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\AttributeGroup;
use App\Models\Bin;
use App\Models\Product;
use App\Models\ProductStockLedger;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Get inventory summary with filters
     */
    public function getInventorySummary(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Product::with('brand')->inStock();

            // Apply filters
            if (isset($filters['status'])) {
                $query->where('stock_status', $filters['status']);
            }

            if (isset($filters['brand_id'])) {
                $query->where('brand_id', $filters['brand_id']);
            }

            if (isset($filters['warehouse_id'])) {
                $query->whereJsonContains('warehouse_info', [
                    'warehouse_id' => (string) $filters['warehouse_id']
                ]);
            }

            if (isset($filters['date_from'])) {
                $query->whereDate('created_at', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->whereDate('created_at', '<=', $filters['date_to']);
            }

            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('title', 'like', "%{$filters['search']}%")
                        ->orWhere('slug', 'like', "%{$filters['search']}%");
                });
            }

            // Sorting
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            // Return paginated or all
            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching inventory summary: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch inventory summary');
        }
    }

    /**
     * Get all stock movements with filters
     */
    public function getAllMovements(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = StockMovement::with([
                'sourceWarehouse',
                'destinationWarehouse',
                'createdBy',
                'items.product'
            ]);

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['source_warehouse_id'])) {
                $query->where('source_warehouse_id', $filters['source_warehouse_id']);
            }

            if (isset($filters['destination_warehouse_id'])) {
                $query->where('destination_warehouse_id', $filters['destination_warehouse_id']);
            }

            if (isset($filters['date_from'])) {
                $query->whereDate('movement_date', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->whereDate('movement_date', '<=', $filters['date_to']);
            }

            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('movement_number', 'like', "%{$filters['search']}%")
                        ->orWhere('notes', 'like', "%{$filters['search']}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'movement_date';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching stock movements: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch stock movements');
        }
    }

    /**
     * Get movement by ID
     */
    public function getMovementById(int $id): StockMovement
    {
        $movement = StockMovement::with([
            'sourceWarehouse',
            'destinationWarehouse',
            'items.product.brand',
            'items.sourceBin',
            'items.destinationBin',
            'createdBy',
            'approvedBy'
        ])->find($id);

        if (!$movement) {
            throw ApiException::notFound('Stock Movement');
        }

        return $movement;
    }

    /**
     * Create stock movement
     */
    public function createMovement(array $data): StockMovement
    {
        DB::beginTransaction();

        try {
            $items = $data['items'];
            unset($data['items']);

            // Validate stock availability
            $this->validateStockAvailability($items, $data['source_warehouse_id']);

            $data['status'] = Status::Pending->value;

            // Create movement (movement_number auto-generated in boot method)
            $movement = StockMovement::create($data);

            // Create movement items
            foreach ($items as $item) {
                $movement->items()->create([
                    'product_id' => $item['product_id'],
                    'batch_number' => $item['batch_number'] ?? null,
                    'source_bin_id' => $item['source_bin_id'] ?? null,
                    'destination_bin_id' => $item['destination_bin_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'serial_numbers' => $item['serial_numbers'] ?? null,
                ]);
            }
            $totalQuantity = collect($items)->sum('quantity');
            LogHelper::created('stock_movement', $movement->id, $movement->company_id, $movement->movement_number .' total quantity ' . $totalQuantity);

            DB::commit();

            Log::info('Stock movement created successfully', [
                'movement_id' => $movement->id,
                'movement_number' => $movement->movement_number
            ]);

            return $this->getMovementById($movement->id);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock movement creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create stock movement: ' . $e->getMessage());
        }
    }

    /**
     * Update stock movement
     */
    public function updateMovement(int $id, array $data): StockMovement
    {
        DB::beginTransaction();

        try {
            $movement = $this->getMovementById($id);

            if ($movement->status !== 'pending') {
                throw ApiException::badRequest('Only pending movements can be updated');
            }

            $items = $data['items'];
            unset($data['items']);

            // Validate stock availability
            $this->validateStockAvailability($items, $data['source_warehouse_id']);

            $movement->update([
                'movement_date' => $data['movement_date'],
                'source_warehouse_id' => $data['source_warehouse_id'],
                'destination_warehouse_id' => $data['destination_warehouse_id'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Delete old items and create new ones
            $movement->items()->delete();

            foreach ($items as $item) {
                $movement->items()->create([
                    'product_id' => $item['product_id'],
                    'batch_number' => $item['batch_number'] ?? null,
                    'source_bin_id' => $item['source_bin_id'] ?? null,
                    'destination_bin_id' => $item['destination_bin_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'serial_numbers' => $item['serial_numbers'] ?? null,
                ]);
            }
            $totalQuantity = collect($items)->sum('quantity');

            LogHelper::updated('stock_movement', $id, $movement->company_id,$movement->movement_number . ' total quantity ' . $totalQuantity);
            DB::commit();

            Log::info('Stock movement updated', ['movement_id' => $id]);

            return $this->getMovementById($movement->id);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock movement update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update stock movement');
        }
    }

    /**
     * Approve stock movement
     */
    public function approveMovement(int $id): StockMovement
    {
        DB::beginTransaction();

        try {
            $movement = StockMovement::with('items.product')->findOrFail($id);

            if ($movement->status !== Status::Pending->value) {
                throw ApiException::badRequest('Only pending movements can be approved');
            }

            foreach ($movement->items as $item) {
                // Decrease from source warehouse
                $this->updateWarehouseStock(
                    $item->product_id,
                    $movement->source_warehouse_id,
                    $item->source_bin_id,
                    -$item->quantity,
                    $item->batch_number,
                    $item->serial_numbers,
                    $movement,
                    ProductStockLedger::TYPE_TRANSFER_OUT,
                );

                // Increase in destination warehouse
                $this->updateWarehouseStock(
                    $item->product_id,
                    $movement->destination_warehouse_id,
                    $item->destination_bin_id,
                    $item->quantity,
                    $item->batch_number,
                    $item->serial_numbers,
                    $movement,
                    ProductStockLedger::TYPE_TRANSFER_IN,
                );

                // Update product's warehouse_info
                $this->updateProductWarehouseInfo(
                    $item->product,
                    $movement->source_warehouse_id,
                    $movement->destination_warehouse_id,
                    $item->source_bin_id,
                    $item->destination_bin_id,
                    $item->quantity
                );
            }

            $movement->update([
                'status' => Status::Approved->value,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            DB::commit();

            Log::info('Stock movement approved', ['movement_id' => $id]);
            LogHelper::custom('approved', 'stock_movement', $id, $movement->company_id,$movement->movement_number .' approved');

            return $this->getMovementById($movement->id);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock movement approval failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to approve stock movement');
        }
    }

    /**
     * Cancel stock movement
     */
    public function cancelMovement(int $id): StockMovement
    {
        DB::beginTransaction();

        try {
            $movement = $this->getMovementById($id);

            if ($movement->status !== Status::Pending->value) {
                throw ApiException::badRequest('Only pending movements can be cancelled');
            }

            if ($movement->status === Status::Cancelled->value) {
                throw ApiException::badRequest('Movement is already cancelled');
            }

            $movement->update(['status' => Status::Cancelled->value]);

            DB::commit();

            Log::info('Stock movement cancelled', ['movement_id' => $id]);
            LogHelper::custom('cancelled', 'stock_movement', $id, $movement->company_id, $movement->movement_number .' movement cancelled');

            return $this->getMovementById($movement->id);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock movement cancellation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to cancel stock movement');
        }
    }

    /**
     * Delete stock movement
     */
    public function deleteMovement(int $id): void
    {
        DB::beginTransaction();

        try {
            $movement = $this->getMovementById($id);

            if ($movement->status === Status::Approved->value) {
                throw ApiException::badRequest('Approved movements cannot be deleted');
            }
            $movement->delete();

            DB::commit();

            Log::info('Stock movement deleted', ['movement_id' => $id]);
            LogHelper::deleted('stock_movement', $id, $movement->company_id, $movement->movement_number .' movement deleted');
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock movement deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete stock movement');
        }
    }

    /**
     * Get products by warehouse
     */
    public function getProductsByWarehouse(int $warehouseId): Collection
    {
        try {
            return Product::whereJsonContains('warehouse_info', [
                'warehouse_id' => (string) $warehouseId
            ])
                ->where('stock_status', 'in_stock')
                ->with('brand')
                ->get()
                ->map(function ($product) use ($warehouseId) {
                    $warehouseInfo = collect($product->warehouse_info ?? [])
                        ->firstWhere('warehouse_id', (string) $warehouseId);

                    $product->available_quantity = $warehouseInfo['quantity'] ?? 0;
                    return $product;
                });
        } catch (\Exception $e) {
            Log::error('Error fetching products by warehouse: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch products');
        }
    }

    /**
     * Get bins by warehouse
     */
    public function getBinsByWarehouse(int $warehouseId): Collection
    {
        try {
            return Bin::where('warehouse_id', $warehouseId)
                ->where('status', 1)
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching bins by warehouse: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch bins');
        }
    }

    /**
     * Validate stock availability
     */
    private function validateStockAvailability(array $items, int $sourceWarehouseId): void
    {
        foreach ($items as $item) {
            $availableStock = $this->getCurrentWarehouseStock(
                $item['product_id'],
                $sourceWarehouseId,
                $item['source_bin_id'] ?? null,
                $item['batch_number'] ?? null
            );

            if ($availableStock < $item['quantity']) {
                $product = Product::find($item['product_id']);
                throw ApiException::badRequest(
                    "Insufficient stock for product: {$product->title}. Available: {$availableStock}, Required: {$item['quantity']}"
                );
            }
        }
    }

    /**
     * Update warehouse stock in ledger
     */
    private function updateWarehouseStock(
        int $productId,
        int $warehouseId,
        ?int $binId,
        int $quantityChange,
        ?string $batchNumber,
        ?array $serialNumbers,
        StockMovement $movement,
        string $type
    ): void {
        $currentStock = $this->getCurrentWarehouseStock($productId, $warehouseId, $binId, $batchNumber);

        ProductStockLedger::create([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'bin_id' => $binId,
            'batch_number' => $batchNumber,
            'serial_numbers' => $serialNumbers,
            'transaction_type' => $type,
            'reference_type' => 'StockMovement',
            'reference_id' => $movement->id,
            'quantity_before' => $currentStock,
            'quantity_change' => $quantityChange,
            'quantity_after' => $currentStock + $quantityChange,
            'notes' => "Stock movement: {$movement->movement_number}",
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Get current warehouse stock
     */
    private function getCurrentWarehouseStock(
        int $productId,
        int $warehouseId,
        ?int $binId = null,
        ?string $batchNumber = null
    ): int {
        $query = ProductStockLedger::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId);

        if ($binId) {
            $query->where('bin_id', $binId);
        }

        if ($batchNumber) {
            $query->where('batch_number', $batchNumber);
        }

        $ledger = $query->latest()->first();
        return $ledger ? $ledger->quantity_after : 0;
    }

    /**
     * Update product's warehouse_info JSON
     */
    private function updateProductWarehouseInfo(
        Product $product,
        int $sourceWarehouseId,
        int $destinationWarehouseId,
        ?int $sourceBinId,
        ?int $destinationBinId,
        int $quantity
    ): void {
        $warehouseInfo = $product->warehouse_info ?? [];

        // Update source warehouse
        foreach ($warehouseInfo as $key => &$info) {
            if ($info['warehouse_id'] == $sourceWarehouseId) {
                if (isset($info['bin_id']) && $info['bin_id'] == $sourceBinId) {
                    $info['quantity'] = max(0, ($info['quantity'] ?? 0) - $quantity);

                    if ($info['quantity'] == 0) {
                        unset($warehouseInfo[$key]);
                    }
                    break;
                } elseif (!isset($info['bin_id']) && !$sourceBinId) {
                    $info['quantity'] = max(0, ($info['quantity'] ?? 0) - $quantity);

                    if ($info['quantity'] == 0) {
                        unset($warehouseInfo[$key]);
                    }
                    break;
                }
            }
        }

        // Update destination warehouse
        $destinationUpdated = false;
        foreach ($warehouseInfo as &$info) {
            if ($info['warehouse_id'] == $destinationWarehouseId) {
                if (isset($destinationBinId) && isset($info['bin_id']) && $info['bin_id'] == $destinationBinId) {
                    $info['quantity'] = ($info['quantity'] ?? 0) + $quantity;
                    $destinationUpdated = true;
                    break;
                } elseif (!isset($destinationBinId) && !isset($info['bin_id'])) {
                    $info['quantity'] = ($info['quantity'] ?? 0) + $quantity;
                    $destinationUpdated = true;
                    break;
                }
            }
        }

        if (!$destinationUpdated) {
            $newInfo = [
                'warehouse_id' => (string) $destinationWarehouseId,
                'quantity' => $quantity,
            ];

            if ($destinationBinId) {
                $newInfo['bin_id'] = (string) $destinationBinId;
            }

            $warehouseInfo[] = $newInfo;
        }

        // Re-index array
        $warehouseInfo = array_values($warehouseInfo);

        $product->update(['warehouse_info' => $warehouseInfo]);

        // Update total stock quantity
        $totalStock = array_sum(array_column($warehouseInfo, 'quantity'));
        $product->update([
            'stock_quantity' => $totalStock,
            'available_stock' => $totalStock,
            'stock_status' => $totalStock > 0 ? 'in_stock' : 'out_of_stock'
        ]);
    }
}
