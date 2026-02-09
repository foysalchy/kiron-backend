<?php

namespace App\Services;

use App\Models\{Product, Gallery, ProductStockLedger, ProductVariation, ProductVariationAttribute, ProductVariationStock, ProductVariationStockLedger};
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
            $query = Product::with(['brand', 'galleries', 'variations.attributes.attributeValue.attributeGroup']);

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
        $product = Product::with(['brand', 'galleries','variations.attributes.attributeValue.attributeGroup'])->find($id);

        if (!$product) {
            throw ApiException::notFound('Product');
        }
        Product::loadCategoriesForCollection(collect([$product]));

        return $product;
    }

    /**
     * Create a new product
     */
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

            // Extract gallery images
            $galleryImages = $data['gallery_images'] ?? [];
            unset($data['gallery_images']);

            // Determine product type and process accordingly
            $productType = $data['type'];

            if ($productType === 'single') {
                // Handle Single Product
                $product = $this->createSingleProduct($data);
            } elseif ($productType === 'variation') {
                // Handle Variation Product
                $product = $this->createVariationProduct($data);
            } else {
                throw ApiException::badRequest('Invalid product type');
            }

            // Upload and create galleries
            if (!empty($galleryImages)) {
                $this->createGalleries($product->id, $galleryImages);
            }

            DB::commit();

            Log::info('Product created successfully', [
                'product_id' => $product->id,
                'type' => $productType
            ]);

            LogHelper::created('product', $product->id, $product->company_id);

            return $product->load([
                'brand',
                'galleries',
                'variations.attributes.attributeGroup',
                'variations.attributes.attributeValue',
                'variations.stocks.warehouse'
            ]);
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
     * Create a single product (no variations)
     */
    private function createSingleProduct(array $data): Product
    {
        // Extract warehouse info
        $warehouseInfo = $data['warehouse_info'] ?? [];
        unset($data['warehouse_info']);

        // Calculate total stock from warehouse_info
        $totalStock = 0;
        if (!empty($warehouseInfo)) {
            $totalStock = collect($warehouseInfo)->sum('quantity');
        }

        // Set stock-related fields for single product
        $data['stock_quantity'] = $totalStock;
        $data['available_stock'] = $totalStock;
        $data['stock_status'] = $totalStock > 0 ? 'in_stock' : 'out_of_stock';
        $data['warehouse_info'] = $warehouseInfo; // Keep as JSON for single products

        // Create the product
        $product = Product::create($data);

        // Create initial stock ledger entries for each warehouse
        if (!empty($warehouseInfo)) {
            foreach ($warehouseInfo as $warehouseStock) {
                if (isset($warehouseStock['quantity']) && $warehouseStock['quantity'] > 0) {
                    ProductStockLedger::create([
                        'product_id' => $product->id,
                        'variation_id' => null, // ✅ Single product has no variation
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
                        'notes' => 'Initial stock entry for single product',
                        'created_by' => Auth::id(),
                    ]);
                }
            }
        }

        return $product;
    }

    /**
     * Create a variation product using NORMALIZED tables
     */
    private function createVariationProduct(array $data): Product
    {
        // Extract variations data
        $variations = $data['variations'] ?? [];
        unset($data['variations']);

        // For variation products, don't store pricing at product level
        $data['regular_price'] = null;
        $data['discount_type'] = null;
        $data['discount'] = null;
        $data['warehouse_info'] = null; // Don't use JSON for variations

        // Calculate total stock (will be updated after creating variations)
        $data['stock_quantity'] = 0;
        $data['available_stock'] = 0;
        $data['stock_status'] = 'out_of_stock';

        // Create the product
        $product = Product::create($data);

        $totalStock = 0;

        // Create each variation
        foreach ($variations as $variationData) {
            $attributes = $variationData['attributes'] ?? [];
            $warehouseInfo = $variationData['warehouse_info'] ?? [];

            $variationData['image'] = FileUploadHelper::uploadImage(
                $variationData['image'],
                'products/variation',
                'public',
                2048
            );
            // Calculate stock for this variation
            $variationStock = collect($warehouseInfo)->sum('quantity');
            $totalStock += $variationStock;

            // Generate unique combination hash for fast lookups
            $combinationHash = $this->generateCombinationHash($product->id, $attributes);

            // Create the variation record
            $variation = ProductVariation::create([
                'product_id' => $product->id,
                'image' => $variationData['image'] ?? null,
                'sku' => $variationData['sku'] ?? null,
                'regular_price' => $variationData['regular_price'],
                'discount_type' => $variationData['discount_type'] ?? 'flat',
                'discount' => $variationData['discount'] ?? 0,
                'stock_quantity' => $variationStock,
                'available_stock' => $variationStock,
                'stock_status' => $variationStock > 0 ? 'in_stock' : 'out_of_stock',
                'combination_hash' => $combinationHash,
            ]);

            // Create variation attributes
            foreach ($attributes as $attribute) {
                ProductVariationAttribute::create([
                    'product_variation_id' => $variation->id,
                    'attribute_group_id' => $attribute['attribute_group_id'],
                    'attribute_value_id' => $attribute['attribute_value_id'],
                ]);
            }

            // Create warehouse stocks for this variation
            foreach ($warehouseInfo as $whStock) {
                if ($whStock['quantity'] > 0) {
                    // Create variation stock record
                    ProductVariationStock::create([
                        'product_variation_id' => $variation->id,
                        'warehouse_id' => $whStock['warehouse_id'],
                        'bin_id' => $whStock['bin_id'] ?? null,
                        'quantity' => $whStock['quantity'],
                    ]);

                    // ✅ Create VARIATION stock ledger entry (not product stock ledger)
                    $variationDisplay = $this->getVariationDisplayName($variation->id);

                    ProductVariationStockLedger::create([
                        'product_id' => $product->id,
                        'variation_id' => $variation->id,
                        'warehouse_id' => $whStock['warehouse_id'],
                        'bin_id' => $whStock['bin_id'] ?? null,
                        'batch_number' => $variation->sku ?? null,
                        'serial_numbers' => null,
                        'transaction_type' => 'initial_stock',
                        'reference_type' => 'ProductVariation',
                        'reference_id' => $variation->id,
                        'quantity_before' => 0,
                        'quantity_change' => $whStock['quantity'],
                        'quantity_after' => $whStock['quantity'],
                        'notes' => "Initial stock for variation: {$variationDisplay}",
                        'created_by' => Auth::id(),
                    ]);
                }
            }
        }

        // Update product total stock
        $product->update([
            'stock_quantity' => $totalStock,
            'available_stock' => $totalStock,
            'stock_status' => $totalStock > 0 ? 'in_stock' : 'out_of_stock',
        ]);

        return $product;
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

            // Update based on product type
            if ($product->type === 'single') {
                $this->updateSingleProduct($product, $data);
            } elseif ($product->type === 'variation') {
                $this->updateVariationProduct($product, $data);
            }

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

            return $product->fresh([
                'brand',
                'galleries',
                'variations.attributes.attributeGroup',
                'variations.attributes.attributeValue',
                'variations.stocks.warehouse'
            ]);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['thumbnail'])) {
                FileUploadHelper::delete($data['thumbnail']);
            }

            Log::error('Product update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update product: ' . $e->getMessage());
        }
    }

    /**
     * Update single product
     */
    private function updateSingleProduct(Product $product, array &$data): void
    {
        $warehouseInfo = $data['warehouse_info'] ?? [];
        unset($data['warehouse_info']);

        $totalStock = collect($warehouseInfo)->sum('quantity');
        $data['stock_quantity'] = $totalStock;
        $data['available_stock'] = $totalStock;
        $data['stock_status'] = $totalStock > 0 ? 'in_stock' : 'out_of_stock';
        $data['warehouse_info'] = $warehouseInfo;
    }

    /**
     * Update variation product
     */
    private function updateVariationProduct(Product $product, array &$data): void
    {
        $variations = $data['variations'] ?? [];
        unset($data['variations']);

        if (empty($variations)) {
            return;
        }

        // Get existing variations
        $existingVariations = ProductVariation::where('product_id', $product->id)
            ->with(['attributes', 'stocks'])
            ->get()
            ->keyBy('id');

        $processedVariationIds = [];
        $totalStock = 0;

        foreach ($variations as $variationData) {
            $variationId = $variationData['id'] ?? null;
            $attributes = $variationData['attributes'] ?? [];
            $warehouseInfo = $variationData['warehouse_info'] ?? [];
            $variationStock = collect($warehouseInfo)->sum('quantity');
            $totalStock += $variationStock;

            $combinationHash = $this->generateCombinationHash($product->id, $attributes);

            if ($variationId && isset($existingVariations[$variationId])) {
                // ✅ Update existing variation
                $variation = $existingVariations[$variationId];

                $oldStock = $variation->available_stock;
                $stockDifference = $variationStock - $oldStock;

                $variation->update([
                    'sku' => $variationData['sku'] ?? $variation->sku,
                    'regular_price' => $variationData['regular_price'],
                    'discount_type' => $variationData['discount_type'] ?? 'flat',
                    'discount' => $variationData['discount'] ?? 0,
                    'stock_quantity' => $variationStock,
                    'available_stock' => $variationStock,
                    'stock_status' => $variationStock > 0 ? 'in_stock' : 'out_of_stock',
                    'combination_hash' => $combinationHash,
                ]);

                // Update attributes (delete old, create new)
                ProductVariationAttribute::where('product_variation_id', $variation->id)->delete();
                foreach ($attributes as $attribute) {
                    ProductVariationAttribute::create([
                        'product_variation_id' => $variation->id,
                        'attribute_group_id' => $attribute['attribute_group_id'],
                        'attribute_value_id' => $attribute['attribute_value_id'],
                    ]);
                }

                // Update warehouse stocks
                $this->updateVariationWarehouseStocks($variation, $warehouseInfo, $stockDifference);

                $processedVariationIds[] = $variationId;
            } else {
                // ✅ Create new variation
                $variation = ProductVariation::create([
                    'product_id' => $product->id,
                    'sku' => $variationData['sku'] ?? null,
                    'regular_price' => $variationData['regular_price'],
                    'discount_type' => $variationData['discount_type'] ?? 'flat',
                    'discount' => $variationData['discount'] ?? 0,
                    'stock_quantity' => $variationStock,
                    'available_stock' => $variationStock,
                    'stock_status' => $variationStock > 0 ? 'in_stock' : 'out_of_stock',
                    'combination_hash' => $combinationHash,
                ]);

                // Create attributes
                foreach ($attributes as $attribute) {
                    ProductVariationAttribute::create([
                        'product_variation_id' => $variation->id,
                        'attribute_group_id' => $attribute['attribute_group_id'],
                        'attribute_value_id' => $attribute['attribute_value_id'],
                    ]);
                }

                // Create warehouse stocks
                foreach ($warehouseInfo as $whStock) {
                    if ($whStock['quantity'] > 0) {
                        ProductVariationStock::create([
                            'product_variation_id' => $variation->id,
                            'warehouse_id' => $whStock['warehouse_id'],
                            'bin_id' => $whStock['bin_id'] ?? null,
                            'quantity' => $whStock['quantity'],
                        ]);

                        // Create stock ledger
                        $variationDisplay = $this->getVariationDisplayName($variation->id);

                        ProductVariationStockLedger::create([
                            'product_id' => $product->id,
                            'variation_id' => $variation->id,
                            'warehouse_id' => $whStock['warehouse_id'],
                            'bin_id' => $whStock['bin_id'] ?? null,
                            'batch_number' => $variation->sku ?? null,
                            'serial_numbers' => null,
                            'transaction_type' => 'initial_stock',
                            'reference_type' => 'ProductVariation',
                            'reference_id' => $variation->id,
                            'quantity_before' => 0,
                            'quantity_change' => $whStock['quantity'],
                            'quantity_after' => $whStock['quantity'],
                            'notes' => "Initial stock for new variation: {$variationDisplay}",
                            'created_by' => Auth::id(),
                        ]);
                    }
                }

                $processedVariationIds[] = $variation->id;
            }
        }

        // ✅ Delete variations that were not in the update request
        $variationsToDelete = $existingVariations->filter(function ($variation) use ($processedVariationIds) {
            return !in_array($variation->id, $processedVariationIds);
        });

        foreach ($variationsToDelete as $variation) {
            // Restore stock before deleting
            if ($variation->available_stock > 0) {
                $variationDisplay = $this->getVariationDisplayName($variation->id);

                foreach ($variation->stocks as $stock) {
                    ProductVariationStockLedger::create([
                        'product_id' => $product->id,
                        'variation_id' => $variation->id,
                        'warehouse_id' => $stock->warehouse_id,
                        'bin_id' => $stock->bin_id,
                        'batch_number' => $variation->sku ?? null,
                        'serial_numbers' => null,
                        'transaction_type' => 'adjustment',
                        'reference_type' => 'VariationDeletion',
                        'reference_id' => $variation->id,
                        'quantity_before' => $stock->quantity,
                        'quantity_change' => -$stock->quantity,
                        'quantity_after' => 0,
                        'notes' => "Variation deleted: {$variationDisplay}",
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            $variation->delete(); // This will cascade delete attributes and stocks
        }

        // Update product total stock
        $data['stock_quantity'] = $totalStock;
        $data['available_stock'] = $totalStock;
        $data['stock_status'] = $totalStock > 0 ? 'in_stock' : 'out_of_stock';
    }

    /**
     * Update warehouse stocks for a variation
     */
    private function updateVariationWarehouseStocks(
        ProductVariation $variation,
        array $warehouseInfo,
        int $stockDifference
    ): void {
        // Get existing stocks
        $existingStocks = ProductVariationStock::where('product_variation_id', $variation->id)
            ->get()
            ->keyBy(function ($stock) {
                return $stock->warehouse_id . '-' . ($stock->bin_id ?? 'null');
            });

        $processedKeys = [];

        foreach ($warehouseInfo as $whStock) {
            $key = $whStock['warehouse_id'] . '-' . ($whStock['bin_id'] ?? 'null');
            $newQuantity = $whStock['quantity'];

            if (isset($existingStocks[$key])) {
                // Update existing stock
                $existingStock = $existingStocks[$key];
                $oldQuantity = $existingStock->quantity;
                $quantityChange = $newQuantity - $oldQuantity;

                if ($quantityChange != 0) {
                    $existingStock->quantity = $newQuantity;
                    $existingStock->save();

                    // Create ledger entry for adjustment
                    $variationDisplay = $this->getVariationDisplayName($variation->id);

                    ProductVariationStockLedger::create([
                        'product_id' => $variation->product_id,
                        'variation_id' => $variation->id,
                        'warehouse_id' => $whStock['warehouse_id'],
                        'bin_id' => $whStock['bin_id'] ?? null,
                        'batch_number' => $variation->sku ?? null,
                        'serial_numbers' => null,
                        'transaction_type' => 'adjustment',
                        'reference_type' => 'ProductUpdate',
                        'reference_id' => $variation->id,
                        'quantity_before' => $oldQuantity,
                        'quantity_change' => $quantityChange,
                        'quantity_after' => $newQuantity,
                        'notes' => "Stock adjusted for variation: {$variationDisplay}",
                        'created_by' => Auth::id(),
                    ]);
                }
            } else {
                // Create new stock entry
                if ($newQuantity > 0) {
                    ProductVariationStock::create([
                        'product_variation_id' => $variation->id,
                        'warehouse_id' => $whStock['warehouse_id'],
                        'bin_id' => $whStock['bin_id'] ?? null,
                        'quantity' => $newQuantity,
                    ]);

                    // Create ledger entry
                    $variationDisplay = $this->getVariationDisplayName($variation->id);

                    ProductVariationStockLedger::create([
                        'product_id' => $variation->product_id,
                        'variation_id' => $variation->id,
                        'warehouse_id' => $whStock['warehouse_id'],
                        'bin_id' => $whStock['bin_id'] ?? null,
                        'batch_number' => $variation->sku ?? null,
                        'serial_numbers' => null,
                        'transaction_type' => 'adjustment',
                        'reference_type' => 'ProductUpdate',
                        'reference_id' => $variation->id,
                        'quantity_before' => 0,
                        'quantity_change' => $newQuantity,
                        'quantity_after' => $newQuantity,
                        'notes' => "New warehouse stock for variation: {$variationDisplay}",
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            $processedKeys[] = $key;
        }

        // Delete warehouse stocks that were removed
        $stocksToDelete = $existingStocks->filter(function ($stock) use ($processedKeys) {
            $key = $stock->warehouse_id . '-' . ($stock->bin_id ?? 'null');
            return !in_array($key, $processedKeys);
        });

        foreach ($stocksToDelete as $stock) {
            if ($stock->quantity > 0) {
                $variationDisplay = $this->getVariationDisplayName($variation->id);

                ProductVariationStockLedger::create([
                    'product_id' => $variation->product_id,
                    'variation_id' => $variation->id,
                    'warehouse_id' => $stock->warehouse_id,
                    'bin_id' => $stock->bin_id,
                    'batch_number' => $variation->sku ?? null,
                    'serial_numbers' => null,
                    'transaction_type' => 'adjustment',
                    'reference_type' => 'WarehouseRemoval',
                    'reference_id' => $variation->id,
                    'quantity_before' => $stock->quantity,
                    'quantity_change' => -$stock->quantity,
                    'quantity_after' => 0,
                    'notes' => "Warehouse stock removed for variation: {$variationDisplay}",
                    'created_by' => Auth::id(),
                ]);
            }

            $stock->delete();
        }
    }

    /**
     * Generate unique hash for attribute combination
     */
    private function generateCombinationHash(int $productId, array $attributes): string
    {
        // Sort attributes by group_id for consistent hashing
        $sorted = collect($attributes)->sortBy('attribute_group_id')->values()->all();
        $hashData = $productId . '-' . json_encode($sorted);
        return hash('sha256', $hashData);
    }

    /**
     * Get display name for variation (with actual attribute values)
     */
    private function getVariationDisplayName(int $variationId): string
    {
        $variation = ProductVariation::with([
            'attributes.attributeGroup',
            'attributes.attributeValue'
        ])->find($variationId);

        if (!$variation) {
            return "Variation #{$variationId}";
        }

        $names = [];
        foreach ($variation->attributes as $attr) {
            $names[] = "{$attr->attributeGroup->name}: {$attr->attributeValue->value}";
        }

        return implode(' / ', $names);
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
     * Adjust stock (Manual Correction)
     * Used for: Stock Count Correction, Manual Adjustment
     */
    /**
     * Adjust stock (Manual Correction)
     * Handles both single and variation products
     */
    public function adjustStock(int $id, array $data): Product
    {
        DB::beginTransaction();

        try {
            $product = $this->getProductById($id);

            $warehouseId = $data['warehouse_id'];
            $binId = $data['bin_id'] ?? null;
            $variationId = $data['variation_id'] ?? null;
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

            // Check product type
            if ($product->type === 'variation') {
                if (!$variationId) {
                    throw ApiException::badRequest('Variation ID is required for variation products');
                }

                return $this->adjustVariationStock(
                    $product,
                    $variationId,
                    $warehouseId,
                    $binId,
                    $adjustmentQuantity,
                    $batchNumber,
                    $serialNumbers,
                    $adjustmentType,
                    $referenceType,
                    $referenceId,
                    $notes
                );
            } else {
                return $this->adjustSingleProductStock(
                    $product,
                    $warehouseId,
                    $binId,
                    $adjustmentQuantity,
                    $batchNumber,
                    $serialNumbers,
                    $adjustmentType,
                    $referenceType,
                    $referenceId,
                    $notes
                );
            }
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
     * Adjust stock for single product
     */
    private function adjustSingleProductStock(
        Product $product,
        int $warehouseId,
        ?int $binId,
        int $adjustmentQuantity,
        ?string $batchNumber,
        ?array $serialNumbers,
        string $adjustmentType,
        string $referenceType,
        ?int $referenceId,
        string $notes
    ): Product {
        // Get current stock
        $currentStock = ProductStockLedger::getCurrentStock(
            $product->id,
            $warehouseId,
            $binId,
            $batchNumber
        );

        $newStock = $currentStock + $adjustmentQuantity;

        if ($newStock < 0) {
            throw ApiException::badRequest(
                "Stock cannot be negative after adjustment. Current: {$currentStock}, Adjustment: {$adjustmentQuantity}"
            );
        }

        // Create ledger entry
        ProductStockLedger::create([
            'product_id' => $product->id,
            'variation_id' => null,
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

        Log::info('Single product stock adjusted', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouseId,
            'previous_stock' => $currentStock,
            'adjustment' => $adjustmentQuantity,
            'new_stock' => $newStock
        ]);

        LogHelper::custom('stock_adjusted', 'product', $product->id, $product->company_id, 'stock adjust quantity => ' . $adjustmentQuantity);

        return $product->fresh();
    }

    /**
     * Adjust stock for product variation
     */
    private function adjustVariationStock(
        Product $product,
        int $variationId,
        int $warehouseId,
        ?int $binId,
        int $adjustmentQuantity,
        ?string $batchNumber,
        ?array $serialNumbers,
        string $adjustmentType,
        string $referenceType,
        ?int $referenceId,
        string $notes
    ): Product {
        // Get variation
        $variation = ProductVariation::find($variationId);

        if (!$variation || $variation->product_id !== $product->id) {
            throw ApiException::badRequest('Invalid variation for this product');
        }

        // Get current stock for variation
        $currentStock = ProductVariationStockLedger::getCurrentStock(
            $variationId,
            $warehouseId,
            $binId,
            $batchNumber
        );

        $newStock = $currentStock + $adjustmentQuantity;

        if ($newStock < 0) {
            throw ApiException::badRequest(
                "Variation stock cannot be negative after adjustment. Current: {$currentStock}, Adjustment: {$adjustmentQuantity}"
            );
        }

        // Create variation stock ledger entry
        ProductVariationStockLedger::create([
            'product_id' => $product->id,
            'variation_id' => $variationId,
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

        // Update or create variation stock record
        $variationStock = ProductVariationStock::firstOrNew([
            'product_variation_id' => $variationId,
            'warehouse_id' => $warehouseId,
            'bin_id' => $binId,
        ]);

        $variationStock->quantity += $adjustmentQuantity;

        if ($variationStock->quantity < 0) {
            $variationStock->quantity = 0;
        }

        $variationStock->save();

        // Update variation available stock and stock status
        $variation->available_stock += $adjustmentQuantity;
        $variation->stock_quantity += $adjustmentQuantity;

        // Ensure stock doesn't go negative
        if ($variation->available_stock < 0) {
            $variation->available_stock = 0;
        }
        if ($variation->stock_quantity < 0) {
            $variation->stock_quantity = 0;
        }

        $variation->stock_status = $variation->available_stock > 0 ? 'in_stock' : 'out_of_stock';
        $variation->save();

        // Recalculate product total stock from all variations
        $this->recalculateTotalStockFromVariations($product);

        DB::commit();

        Log::info('Variation stock adjusted', [
            'product_id' => $product->id,
            'variation_id' => $variationId,
            'warehouse_id' => $warehouseId,
            'previous_stock' => $currentStock,
            'adjustment' => $adjustmentQuantity,
            'new_stock' => $newStock
        ]);

        LogHelper::custom('variation_stock_adjusted', 'product', $product->id, $product->company_id, 'variation_id => ' . $variationId . ', stock adjust quantity => ' . $adjustmentQuantity);

        return $product->fresh(['variations']);
    }

    /**
     * Get stock information for a product (single or variation)
     */
    public function getStockInfo(int $id, ?int $variationId = null, ?int $warehouseId = null): array
    {
        $product = $this->getProductById($id);

        if ($product->type === 'variation') {
            if ($variationId) {
                return $this->getVariationStockInfo($product, $variationId, $warehouseId);
            } else {
                return $this->getAllVariationsStockInfo($product, $warehouseId);
            }
        } else {
            return $this->getSingleProductStockInfo($product, $warehouseId);
        }
    }

    /**
     * Get stock info for single product
     */
    private function getSingleProductStockInfo(Product $product, ?int $warehouseId = null): array
    {
        $query = ProductStockLedger::where('product_id', $product->id);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $ledgers = $query->with(['warehouse', 'bin', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'product_id' => $product->id,
            'product_name' => $product->title,
            'product_type' => 'single',
            'total_stock' => $product->available_stock,
            'stock_status' => $product->stock_status,
            'ledgers' => $ledgers,
        ];
    }

    /**
     * Get stock info for specific variation
     */
    private function getVariationStockInfo(Product $product, int $variationId, ?int $warehouseId = null): array
    {
        $variation = ProductVariation::with(['attributes.attributeGroup', 'attributes.attributeValue'])
            ->find($variationId);

        if (!$variation || $variation->product_id !== $product->id) {
            throw ApiException::badRequest('Invalid variation for this product');
        }

        $query = ProductVariationStockLedger::where('variation_id', $variationId);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $ledgers = $query->with(['warehouse', 'bin', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'product_id' => $product->id,
            'product_name' => $product->title,
            'product_type' => 'variation',
            'variation_id' => $variation->id,
            'variation_sku' => $variation->sku,
            'variation_attributes' => $variation->attributes->map(function ($attr) {
                return [
                    'group' => $attr->attributeGroup->name,
                    'value' => $attr->attributeValue->value,
                ];
            }),
            'total_stock' => $variation->available_stock,
            'stock_status' => $variation->stock_status,
            'ledgers' => $ledgers,
        ];
    }

    /**
     * Get stock info for all variations
     */
    private function getAllVariationsStockInfo(Product $product, ?int $warehouseId = null): array
    {
        $variations = ProductVariation::with(['attributes.attributeGroup', 'attributes.attributeValue'])
            ->where('product_id', $product->id)
            ->get();

        $variationsData = $variations->map(function ($variation) use ($warehouseId) {
            $query = ProductVariationStockLedger::where('variation_id', $variation->id);

            if ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            }

            $totalStock = $query->latest('id')->value('quantity_after') ?? 0;

            return [
                'variation_id' => $variation->id,
                'sku' => $variation->sku,
                'attributes' => $variation->attributes->map(function ($attr) {
                    return [
                        'group' => $attr->attributeGroup->name,
                        'value' => $attr->attributeValue->value,
                    ];
                }),
                'stock' => $totalStock,
                'stock_status' => $variation->stock_status,
                'regular_price' => $variation->regular_price,
                'discount' => $variation->discount,
            ];
        });

        return [
            'product_id' => $product->id,
            'product_name' => $product->title,
            'product_type' => 'variation',
            'total_stock' => $product->available_stock,
            'stock_status' => $product->stock_status,
            'variations' => $variationsData,
        ];
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

    /**
     * Remove stock from warehouse (handles both single and variation products)
     */
    public function removeStockFromWarehouse(int $id, array $data): Product
    {
        DB::beginTransaction();

        try {
            $product = $this->getProductById($id);

            $warehouseId = $data['warehouse_id'];
            $binId = $data['bin_id'] ?? null;
            $quantity = $data['quantity'];
            $variationId = $data['variation_id'] ?? null;
            $batchNumber = $data['batch_number'] ?? null;
            $serialNumbers = $data['serial_numbers'] ?? null;
            $transactionType = $data['transaction_type'] ?? 'sale';
            $referenceType = $data['reference_type'] ?? 'Manual';
            $referenceId = $data['reference_id'] ?? null;
            $notes = $data['notes'] ?? "Stock removed from warehouse";

            if ($quantity <= 0) {
                throw ApiException::badRequest('Quantity must be greater than 0');
            }

            // Check product type
            if ($product->type === 'variation') {
                if (!$variationId) {
                    throw ApiException::badRequest('Variation ID is required for variation products');
                }

                // Handle variation stock
                return $this->removeVariationStock(
                    $product,
                    $variationId,
                    $warehouseId,
                    $binId,
                    $quantity,
                    $batchNumber,
                    $serialNumbers,
                    $transactionType,
                    $referenceType,
                    $referenceId,
                    $notes
                );
            } else {
                // Handle single product stock
                return $this->removeSingleProductStock(
                    $product,
                    $warehouseId,
                    $binId,
                    $quantity,
                    $batchNumber,
                    $serialNumbers,
                    $transactionType,
                    $referenceType,
                    $referenceId,
                    $notes
                );
            }
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
     * Remove stock for single product
     */
    private function removeSingleProductStock(
        Product $product,
        int $warehouseId,
        ?int $binId,
        int $quantity,
        ?string $batchNumber,
        ?array $serialNumbers,
        string $transactionType,
        string $referenceType,
        ?int $referenceId,
        string $notes
    ): Product {
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
            'variation_id' => null,
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

        Log::info('Single product stock removed', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouseId,
            'quantity' => $quantity,
            'remaining_stock' => $newStock
        ]);

        LogHelper::custom('stock_removed', 'product', $product->id, $product->company_id, 'quantity removed => ' . $quantity);

        return $product->fresh();
    }

    /**
     * Remove stock for product variation
     */
    private function removeVariationStock(
        Product $product,
        int $variationId,
        int $warehouseId,
        ?int $binId,
        int $quantity,
        ?string $batchNumber,
        ?array $serialNumbers,
        string $transactionType,
        string $referenceType,
        ?int $referenceId,
        string $notes
    ): Product {
        // Get variation
        $variation = ProductVariation::find($variationId);

        if (!$variation || $variation->product_id !== $product->id) {
            throw ApiException::badRequest('Invalid variation for this product');
        }

        // Get current stock for variation
        $currentStock = ProductVariationStockLedger::getCurrentStock(
            $variationId,
            $warehouseId,
            $binId,
            $batchNumber
        );

        if ($currentStock < $quantity) {
            throw ApiException::badRequest(
                "Insufficient variation stock. Available: {$currentStock}, Requested: {$quantity}"
            );
        }

        $newStock = $currentStock - $quantity;

        // Create variation stock ledger entry
        ProductVariationStockLedger::create([
            'product_id' => $product->id,
            'variation_id' => $variationId,
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

        // Update variation stock in product_variation_stocks
        $variationStock = ProductVariationStock::where('product_variation_id', $variationId)
            ->where('warehouse_id', $warehouseId)
            ->where('bin_id', $binId)
            ->first();

        if ($variationStock) {
            $variationStock->quantity -= $quantity;
            $variationStock->save();
        }

        // Update variation available stock and stock status
        $variation->available_stock -= $quantity;
        $variation->stock_status = $variation->available_stock > 0 ? 'in_stock' : 'out_of_stock';
        $variation->save();

        // Recalculate product total stock from all variations
        $this->recalculateTotalStockFromVariations($product);

        DB::commit();

        Log::info('Variation stock removed', [
            'product_id' => $product->id,
            'variation_id' => $variationId,
            'warehouse_id' => $warehouseId,
            'quantity' => $quantity,
            'remaining_stock' => $newStock
        ]);

        LogHelper::custom('variation_stock_removed', 'product', $product->id, $product->company_id, 'variation_id => ' . $variationId . ', quantity removed => ' . $quantity);

        return $product->fresh(['variations']);
    }

    /**
     * Add stock to warehouse (handles both single and variation products)
     */
    public function addStockToWarehouse(int $id, array $data): Product
    {
        DB::beginTransaction();

        try {
            $product = $this->getProductById($id);

            $warehouseId = $data['warehouse_id'];
            $binId = $data['bin_id'] ?? null;
            $quantity = $data['quantity'];
            $variationId = $data['variation_id'] ?? null;
            $batchNumber = $data['batch_number'] ?? null;
            $serialNumbers = $data['serial_numbers'] ?? null;
            $transactionType = $data['transaction_type'] ?? 'purchase';
            $referenceType = $data['reference_type'] ?? 'Manual';
            $referenceId = $data['reference_id'] ?? null;
            $notes = $data['notes'] ?? "Stock added to warehouse";

            if ($quantity <= 0) {
                throw ApiException::badRequest('Quantity must be greater than 0');
            }

            // Check product type
            if ($product->type === 'variation') {
                if (!$variationId) {
                    throw ApiException::badRequest('Variation ID is required for variation products');
                }

                return $this->addVariationStock(
                    $product,
                    $variationId,
                    $warehouseId,
                    $binId,
                    $quantity,
                    $batchNumber,
                    $serialNumbers,
                    $transactionType,
                    $referenceType,
                    $referenceId,
                    $notes
                );
            } else {
                return $this->addSingleProductStock(
                    $product,
                    $warehouseId,
                    $binId,
                    $quantity,
                    $batchNumber,
                    $serialNumbers,
                    $transactionType,
                    $referenceType,
                    $referenceId,
                    $notes
                );
            }
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
     * Add stock for single product
     */
    private function addSingleProductStock(
        Product $product,
        int $warehouseId,
        ?int $binId,
        int $quantity,
        ?string $batchNumber,
        ?array $serialNumbers,
        string $transactionType,
        string $referenceType,
        ?int $referenceId,
        string $notes
    ): Product {
        $currentStock = ProductStockLedger::getCurrentStock(
            $product->id,
            $warehouseId,
            $binId,
            $batchNumber
        );

        $newStock = $currentStock + $quantity;

        ProductStockLedger::create([
            'product_id' => $product->id,
            'variation_id' => null,
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

        $this->updateProductWarehouseInfo(
            $product,
            $warehouseId,
            $binId,
            $quantity,
            'add'
        );

        $this->recalculateTotalStock($product);

        DB::commit();

        Log::info('Single product stock added', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouseId,
            'quantity' => $quantity,
            'new_stock' => $newStock
        ]);

        LogHelper::custom('stock_added', 'product', $product->id, $product->company_id, 'quantity added => ' . $quantity);

        return $product->fresh();
    }

    /**
     * Add stock for product variation
     */
    private function addVariationStock(
        Product $product,
        int $variationId,
        int $warehouseId,
        ?int $binId,
        int $quantity,
        ?string $batchNumber,
        ?array $serialNumbers,
        string $transactionType,
        string $referenceType,
        ?int $referenceId,
        string $notes
    ): Product {
        $variation = ProductVariation::find($variationId);

        if (!$variation || $variation->product_id !== $product->id) {
            throw ApiException::badRequest('Invalid variation for this product');
        }

        $currentStock = ProductVariationStockLedger::getCurrentStock(
            $variationId,
            $warehouseId,
            $binId,
            $batchNumber
        );

        $newStock = $currentStock + $quantity;

        ProductVariationStockLedger::create([
            'product_id' => $product->id,
            'variation_id' => $variationId,
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

        // Update or create variation stock record
        $variationStock = ProductVariationStock::firstOrNew([
            'product_variation_id' => $variationId,
            'warehouse_id' => $warehouseId,
            'bin_id' => $binId,
        ]);

        $variationStock->quantity += $quantity;
        $variationStock->save();

        // Update variation available stock
        $variation->available_stock += $quantity;
        $variation->stock_quantity += $quantity;
        $variation->stock_status = 'in_stock';
        $variation->save();

        // Recalculate product total stock
        $this->recalculateTotalStockFromVariations($product);

        DB::commit();

        Log::info('Variation stock added', [
            'product_id' => $product->id,
            'variation_id' => $variationId,
            'warehouse_id' => $warehouseId,
            'quantity' => $quantity,
            'new_stock' => $newStock
        ]);

        LogHelper::custom('variation_stock_added', 'product', $product->id, $product->company_id, 'variation_id => ' . $variationId . ', quantity added => ' . $quantity);

        return $product->fresh(['variations']);
    }

    /**
     * Recalculate total stock from all variations
     */
    private function recalculateTotalStockFromVariations(Product $product): void
    {
        $totalStock = ProductVariation::where('product_id', $product->id)
            ->sum('available_stock');

        $product->available_stock = $totalStock;
        $product->stock_quantity = $totalStock;
        $product->stock_status = $totalStock > 0 ? 'in_stock' : 'out_of_stock';
        $product->save();
    }
}
