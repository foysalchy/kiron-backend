<?php

namespace App\Services;

use App\Models\{Product, Gallery, ProductStockLedger};
use App\Exceptions\ApiException;
use App\Helpers\{FileUploadHelper, LogHelper};
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{Auth, DB, Log};

class ProductService
{
    /**
     * Get all products with optional pagination
     */
    public function getAllProducts(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Product::with(['brand', 'galleries']);

            if (isset($filters['brand_id'])) {
                $query->where('brand_id', $filters['brand_id']);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['type'])) {
                $query->where('type', $filters['type']);
            }

            if (isset($filters['stock_status'])) {
                $query->where('stock_status', $filters['stock_status']);
            }

            if (isset($filters['purpose'])) {
                if ($filters['purpose'] === 'website') {
                    $query->where('purpose_website', true);
                } elseif ($filters['purpose'] === 'pos') {
                    $query->where('purpose_pos', true);
                }
            }

            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('title', 'like', "%{$filters['search']}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);


            if ($paginate) {
                $result = $query->paginate($filters['per_page'] ?? 15);
                Product::loadCategoriesForCollection($result->getCollection());
                return $result;
            } else {
                $result = $query->get();
                Product::loadCategoriesForCollection($result);
                return $result;
            }
        } catch (\Exception $e) {
            Log::error('Error fetching products: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch products');
        }
    }


    /**
     * Get product by ID
     */
    public function getProductById(int $id): Product
    {
        $product = Product::with(['brand', 'galleries'])->find($id);

        if (!$product) {
            throw ApiException::notFound('Product');
        }
        Product::loadCategoriesForCollection(collect([$product]));

        return $product;
    }

    /**
     * Create a new product
     */
    public function createProduct(array $data): Product
    {
        DB::beginTransaction();

        try {
            // Handle thumbnail upload
            if (isset($data['thumbnail'])) {
                $data['thumbnail'] = FileUploadHelper::uploadImage(
                    $data['thumbnail'],
                    'products/thumbnails',
                    'public',
                    2048
                );
            }

            // Extract gallery and stock data
            $galleryImages = $data['gallery_images'] ?? [];
            $warehouseInfo = $data['warehouse_info'] ?? [];
            unset($data['gallery_images'], $data['warehouse_info']);

            // Calculate total stock from warehouse_info
            $totalStock = 0;
            if (!empty($warehouseInfo)) {
                $totalStock = collect($warehouseInfo)->sum('quantity');
                $data['stock_quantity'] = $totalStock;
                $data['available_stock'] = $totalStock;
                $data['stock_status'] = $totalStock > 0 ? 'in_stock' : 'out_of_stock';
                $data['warehouse_info'] = $warehouseInfo;
            }

            // Create product
            $product = Product::create($data);

            // Create initial stock ledger entries for each warehouse
            if (!empty($warehouseInfo)) {
                foreach ($warehouseInfo as $warehouseStock) {
                    if (isset($warehouseStock['quantity']) && $warehouseStock['quantity'] > 0) {
                        ProductStockLedger::create([
                            'product_id' => $product->id,
                            'warehouse_id' => $warehouseStock['warehouse_id'],
                            'bin_id' => $warehouseStock['bin_id'] ?? null,
                            'batch_number' => null,
                            'serial_numbers' => null,
                            'transaction_type' => 'initial_stock',
                            'reference_type' => 'Product',
                            'reference_id' => $product->id,
                            'quantity_before' => 0,
                            'quantity_change' => $warehouseStock['quantity'],
                            'quantity_after' => $warehouseStock['quantity'],
                            'notes' => 'Initial stock entry for new product',
                            'created_by' => Auth::id(),
                        ]);
                    }
                }
            }

            // Upload and create galleries
            if (!empty($galleryImages)) {
                $this->createGalleries($product->id, $galleryImages);
            }

            DB::commit();

            Log::info('Product created successfully', [
                'product_id' => $product->id,
                'total_stock' => $totalStock
            ]);
            LogHelper::created('product', $product->id, $product->company_id);

            return $product->load(['brand', 'galleries']);
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['thumbnail'])) {
                FileUploadHelper::delete($data['thumbnail']);
            }

            Log::error('Product creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create product: ' . $e->getMessage());
        }
    }

    /**
     * Update product
     */
    public function updateProduct(int $id, array $data): Product
    {
        DB::beginTransaction();

        try {
            $product = $this->getProductById($id);

            // Handle thumbnail upload
            if (isset($data['thumbnail'])) {
                $data['thumbnail'] = FileUploadHelper::replace(
                    $data['thumbnail'],
                    $product->thumbnail,
                    'products/thumbnails'
                );
            }

            // Extract gallery data
            $galleryImages = $data['gallery_images'] ?? [];
            $deleteGalleryIds = $data['deleted_gallery_ids'] ?? [];
            unset($data['gallery_images']);
            unset($data['deleted_gallery_ids']);

            // Update product
            $product->update($data);

            // Delete specified galleries
            if (!empty($deleteGalleryIds)) {
                $this->deleteGalleries($deleteGalleryIds);
            }

            // Add new galleries
            if (!empty($galleryImages)) {
                $this->createGalleries($product->id, $galleryImages);
            }

            DB::commit();

            Log::info('Product updated successfully', ['product_id' => $product->id]);
            LogHelper::updated('product', $product->id, $product->company_id);

            return $product->fresh(['brand', 'galleries']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['thumbnail'])) {
                FileUploadHelper::delete($data['thumbnail']);
            }

            Log::error('Product update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update product');
        }
    }

    /**
     * Delete product (soft delete)
     */
    public function deleteProduct(int $id): bool
    {
        try {
            $product = $this->getProductById($id);
            $product->delete();

            Log::info('Product deleted successfully', ['product_id' => $id]);
            LogHelper::deleted('product', $id, $product->company_id);

            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Product deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete product');
        }
    }

    /**
     * Restore soft deleted product
     */
    public function restoreProduct(int $id): Product
    {
        try {
            $product = Product::withTrashed()->find($id);

            if (!$product) {
                throw ApiException::notFound('Product');
            }

            $product->restore();

            Log::info('Product restored successfully', ['product_id' => $id]);
            LogHelper::restored('product', $id, $product->company_id);

            return $product->load(['brand', 'galleries']);
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Product restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore product');
        }
    }

    /**
     * Permanently delete product
     */
    public function forceDeleteProduct(int $id): bool
    {
        DB::beginTransaction();

        try {
            $product = Product::withTrashed()->with('galleries')->find($id);

            if (!$product) {
                throw ApiException::notFound('Product');
            }

            // Delete thumbnail
            FileUploadHelper::delete($product->thumbnail);

            // Delete all gallery images
            foreach ($product->galleries as $gallery) {
                FileUploadHelper::delete($gallery->image);
            }

            // Delete galleries from database
            $product->galleries()->delete();

            // Delete product
            $product->forceDelete();

            DB::commit();

            Log::info('Product permanently deleted', ['product_id' => $id]);
            LogHelper::forceDeleted('product', $id, $product->company_id);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Product permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete product');
        }
    }

    /**
     * Toggle product status
     */
    public function toggleStatus(int $id): Product
    {
        try {
            $product = $this->getProductById($id);
            $product->update(['status' => !$product->status]);

            Log::info('Product status toggled', ['product_id' => $id]);
            LogHelper::statusChanged('product', $id, $product->company_id);

            return $product->load(['brand', 'galleries']);
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Product status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }

    /**
     * Update stock
     */
    public function updateStock(int $id, array $data): Product
    {
        DB::beginTransaction();

        try {
            $product = $this->getProductById($id);

            $updateData = [
                'stock_status' => $data['stock_status'],
            ];

            // 🔹 Sum with previous stock
            if (array_key_exists('stock_quantity', $data)) {
                $updateData['stock_quantity'] =
                    $product->stock_quantity + $data['stock_quantity'];
            }

            $product->update($updateData);

            Log::info('Product stock updated', [
                'product_id' => $id,
                'previous_stock' => $product->stock_quantity,
                'added_stock' => $data['stock_quantity'] ?? 0,
                'current_stock' => $updateData['stock_quantity'] ?? $product->stock_quantity,
            ]);

            LogHelper::custom(
                'stock_updated',
                'product',
                $id,
                $product->company_id
            );

            DB::commit();

            return $product->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Product stock update failed', [
                'product_id' => $id,
                'error' => $e->getMessage(),
            ]);

            throw ApiException::serverError('Failed to update stock');
        }
    }


    /**
     * Create galleries for product
     */
    private function createGalleries(int $productId, array $images, array $titles = []): void
    {
        foreach ($images as $index => $image) {
            $imagePath = FileUploadHelper::uploadImage(
                $image,
                'products/galleries',
                'public',
                2048
            );

            Gallery::create([
                'product_id' => $productId,
                'image' => $imagePath
            ]);
        }
    }

    /**
     * Delete galleries by IDs
     */
    private function deleteGalleries(array $galleryIds): void
    {
        $galleries = Gallery::whereIn('id', $galleryIds)->get();

        foreach ($galleries as $gallery) {
            FileUploadHelper::delete($gallery->image);
            $gallery->delete();
        }
    }

    /**
     * Add stock to warehouse
     * Used for: Purchase, Initial Stock, Stock Receipt
     */
    public function addStockToWarehouse(int $id, array $data): Product
    {
        DB::beginTransaction();

        try {
            $product = $this->getProductById($id);

            $warehouseId = $data['warehouse_id'];
            $binId = $data['bin_id'] ?? null;
            $quantity = $data['quantity'];
            $batchNumber = $data['batch_number'] ?? null;
            $serialNumbers = $data['serial_numbers'] ?? null;
            $transactionType = $data['transaction_type'] ?? 'purchase';
            $referenceType = $data['reference_type'] ?? 'Manual';
            $referenceId = $data['reference_id'] ?? null;
            $notes = $data['notes'] ?? "Stock added to warehouse";

            if ($quantity <= 0) {
                throw ApiException::badRequest('Quantity must be greater than 0');
            }

            // Get current stock from ledger
            $currentStock = ProductStockLedger::getCurrentStock(
                $product->id,
                $warehouseId,
                $binId,
                $batchNumber
            );
            $newStock = $currentStock + $quantity;

            // Create ledger entry
            ProductStockLedger::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'bin_id' => $binId,
                'batch_number' => $batchNumber,
                'serial_numbers' => $serialNumbers,
                'transaction_type' => $transactionType,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'quantity_before' => $currentStock,
                'quantity_change' => $quantity,
                'quantity_after' => $newStock,
                'notes' => $notes,
                'created_by' => Auth::id(),
            ]);

            // Update warehouse_info
            $this->updateProductWarehouseInfo(
                $product,
                $warehouseId,
                $binId,
                $quantity,
                'add'
            );

            // Recalculate total stock
            $this->recalculateTotalStock($product);

            DB::commit();

            Log::info('Stock added to warehouse', [
                'product_id' => $id,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity,
                'new_stock' => $newStock
            ]);

            LogHelper::custom('stock_added', 'product', $id, $product->company_id, 'quantity added => ' . $quantity);

            return $product->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Add stock failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to add stock: ' . $e->getMessage());
        }
    }

    /**
     * Remove stock from warehouse
     * Used for: Sale, Stock Issue, Damage, Loss
     */
    public function removeStockFromWarehouse(int $id, array $data): Product
    {
        DB::beginTransaction();
        \Log::info($data);
        try {
            $product = $this->getProductById($id);

            $warehouseId = $data['warehouse_id'];
            $binId = $data['bin_id'] ?? null;
            $quantity = $data['quantity'];
            $batchNumber = $data['batch_number'] ?? null;
            $serialNumbers = $data['serial_numbers'] ?? null;
            $transactionType = $data['transaction_type'] ?? 'sale';
            $referenceType = $data['reference_type'] ?? 'Manual';
            $referenceId = $data['reference_id'] ?? null;
            $notes = $data['notes'] ?? "Stock removed from warehouse";

            if ($quantity <= 0) {
                throw ApiException::badRequest('Quantity must be greater than 0');
            }

            // Get current stock
            $currentStock = ProductStockLedger::getCurrentStock(
                $product->id,
                $warehouseId,
                $binId,
                $batchNumber
            );

            if ($currentStock < $quantity) {
                throw ApiException::badRequest(
                    "Insufficient stock. Available: {$currentStock}, Requested: {$quantity}"
                );
            }

            $newStock = $currentStock - $quantity;

            // Create ledger entry
            ProductStockLedger::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'bin_id' => $binId,
                'batch_number' => $batchNumber,
                'serial_numbers' => $serialNumbers,
                'transaction_type' => $transactionType,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'quantity_before' => $currentStock,
                'quantity_change' => -$quantity,
                'quantity_after' => $newStock,
                'notes' => $notes,
                'created_by' => Auth::id(),
            ]);

            // Update warehouse_info
            $this->updateProductWarehouseInfo(
                $product,
                $warehouseId,
                $binId,
                -$quantity,
                'remove'
            );

            // Recalculate total stock
            $this->recalculateTotalStock($product);

            DB::commit();

            Log::info('Stock removed from warehouse', [
                'product_id' => $id,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity,
                'remaining_stock' => $newStock
            ]);

            LogHelper::custom('stock_removed', 'product', $id, $product->company_id, 'quantity removed => ' . $quantity);

            return $product->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Remove stock failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to remove stock: ' . $e->getMessage());
        }
    }

    /**
     * Adjust stock (Manual Correction)
     * Used for: Stock Count Correction, Manual Adjustment
     */
    public function adjustStock(int $id, array $data): Product
    {
        DB::beginTransaction();

        try {
            $product = $this->getProductById($id);

            $warehouseId = $data['warehouse_id'];
            $binId = $data['bin_id'] ?? null;
            $batchNumber = $data['batch_number'] ?? null;
            $serialNumbers = $data['serial_numbers'] ?? null;
            $adjustmentQuantity = $data['quantity']; // Can be + or -
            $adjustmentType = $data['transaction_type'] ?? 'adjustment';
            $referenceType = $data['reference_type'] ?? 'StockAdjustment';
            $referenceId = $data['reference_id'] ?? null;
            $notes = $data['notes'] ?? 'Manual stock adjustment';


            if ($adjustmentQuantity == 0) {
                throw ApiException::badRequest('Adjustment quantity cannot be zero');
            }

            // Get current stock
            $currentStock = ProductStockLedger::getCurrentStock(
                $product->id,
                $warehouseId,
                $binId
            );

            $newStock = $currentStock + $adjustmentQuantity;

            if ($newStock < 0) {
                throw ApiException::badRequest('Stock cannot be negative after adjustment');
            }

            // Create ledger entry
            ProductStockLedger::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'bin_id' => $binId,
                'batch_number' => $batchNumber,
                'serial_numbers' => $serialNumbers,
                'transaction_type' => $adjustmentType,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'quantity_before' => $currentStock,
                'quantity_change' => $adjustmentQuantity,
                'quantity_after' => $newStock,
                'notes' => $notes,
                'created_by' => Auth::id(),
            ]);

            // Update warehouse_info
            $this->updateProductWarehouseInfo(
                $product,
                $warehouseId,
                $binId,
                $adjustmentQuantity,
                $adjustmentQuantity > 0 ? 'add' : 'remove'
            );

            // Recalculate total stock
            $this->recalculateTotalStock($product);

            DB::commit();

            Log::info('Stock adjusted', [
                'product_id' => $id,
                'warehouse_id' => $warehouseId,
                'previous_stock' => $currentStock,
                'adjustment' => $adjustmentQuantity,
                'new_stock' => $newStock
            ]);

            LogHelper::custom('stock_adjusted', 'product', $id, $product->company_id, 'stock adjust quantity =>' . $adjustmentQuantity);

            return $product->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock adjustment failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to adjust stock: ' . $e->getMessage());
        }
    }

    /**
     * Get stock history for a product
     */
    public function getStockHistory(int $id, array $filters = []): Collection
    {
        try {
            $product = $this->getProductById($id);

            $query = ProductStockLedger::where('product_id', $product->id)
                ->with(['warehouse', 'bin', 'creator']);

            if (isset($filters['warehouse_id'])) {
                $query->where('warehouse_id', $filters['warehouse_id']);
            }

            if (isset($filters['transaction_type'])) {
                $query->where('transaction_type', $filters['transaction_type']);
            }

            if (isset($filters['date_from'])) {
                $query->whereDate('created_at', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->whereDate('created_at', '<=', $filters['date_to']);
            }

            return $query->orderBy('created_at', 'desc')
                ->limit($filters['limit'] ?? 50)
                ->get();
        } catch (\Exception $e) {
            Log::error('Get stock history failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to get stock history');
        }
    }

    /**
     * Get current stock by warehouse
     */
    public function getCurrentStockByWarehouse(int $id, int $warehouseId): array
    {
        try {
            $product = $this->getProductById($id);

            $warehouseInfo = collect($product->warehouse_info ?? [])
                ->firstWhere('warehouse_id', (string) $warehouseId);

            $stockInWarehouse = $warehouseInfo['quantity'] ?? 0;

            // Get from ledger for verification
            $ledgerStock = ProductStockLedger::getCurrentStock(
                $product->id,
                $warehouseId
            );

            return [
                'product_id' => $product->id,
                'product_title' => $product->title,
                'warehouse_id' => $warehouseId,
                'stock_quantity' => $stockInWarehouse,
                'ledger_stock' => $ledgerStock,
                'match' => $stockInWarehouse === $ledgerStock
            ];
        } catch (\Exception $e) {
            Log::error('Get warehouse stock failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to get warehouse stock');
        }
    }

    // ========================================
    // PRIVATE HELPER METHODS
    // ========================================

    /**
     * Update product's warehouse_info JSON
     */
    private function updateProductWarehouseInfo(
        Product $product,
        int $warehouseId,
        ?int $binId,
        int $quantityChange,
        string $operation // 'add' or 'remove'
    ): void {
        $warehouseInfo = $product->warehouse_info ?? [];

        $updated = false;

        foreach ($warehouseInfo as $key => &$info) {
            $warehouseMatch = $info['warehouse_id'] == $warehouseId;
            $binMatch = (!$binId && !isset($info['bin_id'])) ||
                ($binId && isset($info['bin_id']) && $info['bin_id'] == $binId);

            if ($warehouseMatch && $binMatch) {
                $currentQty = $info['quantity'] ?? 0;

                if ($operation === 'add') {
                    $info['quantity'] = $currentQty + abs($quantityChange);
                } else {
                    $info['quantity'] = max(0, $currentQty - abs($quantityChange));
                }

                // Remove entry if quantity becomes 0
                if ($info['quantity'] <= 0) {
                    unset($warehouseInfo[$key]);
                }

                $updated = true;
                break;
            }
        }

        // If not found and adding stock, create new entry
        if (!$updated && $operation === 'add') {
            $newInfo = [
                'warehouse_id' => (string) $warehouseId,
                'quantity' => abs($quantityChange),
            ];

            if ($binId) {
                $newInfo['bin_id'] = (string) $binId;
            }

            $warehouseInfo[] = $newInfo;
        }

        // Re-index array
        $warehouseInfo = array_values($warehouseInfo);

        $product->update(['warehouse_info' => $warehouseInfo]);
    }

    /**
     * Recalculate total stock from warehouse_info
     */
    private function recalculateTotalStock(Product $product): void
    {
        $totalStock = collect($product->warehouse_info ?? [])->sum('quantity');

        $product->update([
            'stock_quantity' => $totalStock,
            'available_stock' => $totalStock,
            'stock_status' => $totalStock > 0 ? 'in_stock' : 'out_of_stock'
        ]);
    }
}
