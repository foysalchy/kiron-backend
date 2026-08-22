<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\{Bin, Product, Gallery, ProductStockLedger, ProductVariation, ProductVariationAttribute, ProductVariationStock, ProductVariationStockLedger, User, VariationGallery, Warehouse};
use App\Exceptions\ApiException;
use App\Helpers\{FileUploadHelper, LogHelper};
use App\Notifications\ProductAssignedNotification;
use App\Notifications\ProductUnassignedNotification;
use App\Services\Notification\NotificationRecipientResolver;
use App\Services\Notification\NotificationService;
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
            $query = Product::with([
                'brand',
                'galleries',
                'barcode',
                'variations' => function ($query) {
                    $query->orderBy('regular_price', 'asc');
                },
                'variations.barcode',

                'variations.attributes.attributeGroup',
                'variations.attributes.attributeValue',
                'variations.stocks.warehouse',
                'variations.stocks.warehouse',
                'variations.stocks.bin.area',
                'variations.stocks.bin.rack',
                'variations.stocks.bin.cell',

            ]);

            if (isset($filters['brand_id'])) {
                $query->where('brand_id', $filters['brand_id']);
            }
            if (isset($filters['warehouse_id'])) {
                $warehouseId = $filters['warehouse_id'];

                $query->where(function ($q) use ($warehouseId) {

                    // Single product
                    $q->where(function ($sub) use ($warehouseId) {
                        $sub->where('type', 'single')
                            ->whereRaw("JSON_SEARCH(warehouse_info, 'one', ?) IS NOT NULL", [(string)$warehouseId]);
                    })

                        // Variation product
                        ->orWhere(function ($sub) use ($warehouseId) {
                            $sub->where('type', 'variation')
                                ->whereHas('variations.stocks', function ($stockQuery) use ($warehouseId) {
                                    $stockQuery->where('warehouse_id', $warehouseId);
                                });
                        });
                });
            }
            if (!empty($filters['mega_category_id'])) {

                $categoryIds = array_map('intval', $filters['mega_category_id']);

                $query->where(function ($q) use ($categoryIds) {
                    foreach ($categoryIds as $id) {
                        $q->orWhereJsonContains('mega_category_ids', $id);
                    }
                });
            }

            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }


            if (isset($filters['type'])) {
                $query->where('type', $filters['type']);
            }

            if (isset($filters['stock_status'])) {
                $query->where('stock_status', $filters['stock_status']);
            }

            if (isset($filters['purpose'])) {
                if ($filters['purpose'] === 'website') {
                    $query->where('purpose', 'website');
                } elseif ($filters['purpose'] === 'pos') {
                    $query->where('purpose', 'pos');
                }
            }

            if (isset($filters['source'])) {
                if ($filters['source'] === 'website') {
                    $query->whereNull('source_info');
                } else {
                    $query->where('source_info->source_name', $filters['source']);
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
                $items = $result->getCollection();
            } else {
                $result = $query->get();
                $items = $result;
            }

            Product::loadCategoriesForCollection($items);

            // Single Product
            $warehouseIds = [];
            $binIds = [];


            foreach ($items as $item) {
                if ($item->type === 'single' && is_array($item->warehouse_info)) {
                    foreach ($item->warehouse_info as $info) {
                        if (!empty($info['warehouse_id'])) $warehouseIds[] = $info['warehouse_id'];
                        if (!empty($info['bin_id'])) $binIds[] = $info['bin_id'];
                    }
                }
            }

            $warehouses = Warehouse::whereIn('id', array_unique($warehouseIds))->pluck('name', 'id');
            $bins = Bin::with(['area', 'rack', 'cell'])->whereIn('id', array_unique($binIds))->get()->keyBy('id');


            foreach ($items as $item) {
                if ($item->type === 'single' && is_array($item->warehouse_info)) {
                    $parsedInfo = array_map(function ($info) use ($warehouses, $bins) {
                        $info['warehouse_name'] = $warehouses[$info['warehouse_id'] ?? null] ?? 'Unknown Warehouse';

                        if (!empty($info['bin_id']) && isset($bins[$info['bin_id']])) {
                            $bin = $bins[$info['bin_id']];
                            $info['bin_details'] = [
                                'name' => $bin->name ?? '',
                                'area' => $bin->area->name ?? '',
                                'rack' => $bin->rack->name ?? '',
                                'cell' => $bin->cell->name ?? '',
                            ];
                        } else {
                            $info['bin_details'] = null;
                        }
                        return $info;
                    }, $item->warehouse_info);


                    $item->setAttribute('parsed_warehouse_info', $parsedInfo);
                }
            }

            return $result;
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
        $product = Product::with([
            'brand',
            'galleries',
            'variations.attributes.attributeValue.attributeGroup',
            'variations.stocks.warehouse',
            'variations.galleries',

        ])->find($id);

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
            if (!empty($product->assigned_to)) {
                $assignedUser = User::find($product->assigned_to);

                if ($assignedUser) {
                    $actor = auth()->user();
                    $recipients = collect([$assignedUser]);

                    $isActorCompanySuperAdmin = $actor && !is_null($actor->company_id) && is_null($actor->role);

                    if (!is_null($product->company_id) && !$isActorCompanySuperAdmin) {
                        $companySuperAdmins = NotificationRecipientResolver::companySuperAdmin($product->company_id);
                        $recipients = $recipients->concat($companySuperAdmins);
                    }

                    NotificationService::notify(
                        $recipients,
                        new ProductAssignedNotification(
                            $product->id,
                            $product->title,
                            $product->company_id,
                            $assignedUser->id,
                        )
                    );
                }
            }

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
    public function cloneProduct(Product $product): Product
    {
        DB::beginTransaction();

        try {
            $originalProduct = $product->load([
                'galleries',
                'variations.attributes',
                'variations.stocks.warehouse',
                'variations.stocks.bin',
                'variations.galleries',
            ]);

            // Clone main product data
            $newProductData = $originalProduct->toArray();

            unset(
                $newProductData['id'],
                $newProductData['created_at'],
                $newProductData['updated_at'],
                $newProductData['galleries'],
                $newProductData['variations'],
            );


            $baseSlug = $originalProduct->slug . '_2';
            $newProductData['slug'] = $baseSlug;
            $counter = 2;
            while (Product::where('slug', $newProductData['slug'])->exists()) {
                $newProductData['slug'] = $baseSlug . '_' . $counter;
                $counter++;
            }

            // Thumbnail copy
            if ($originalProduct->thumbnail) {
                $newProductData['thumbnail'] = FileUploadHelper::copyFile(
                    $originalProduct->thumbnail,
                    'products/thumbnails'
                );
            }
            if (!empty($newProductData['sku_code'])) {
                $baseSku = $originalProduct->sku_code . '_copy';
                $newProductData['sku_code'] = $baseSku;
                $counter = 2;
                while (Product::where('sku_code', $newProductData['sku_code'])
                    ->where('company_id', $newProductData['company_id'])
                    ->exists()
                ) {
                    $newProductData['sku_code'] = $baseSku . '_' . $counter;
                    $counter++;
                }
            }
            $newProduct = Product::create($newProductData);

            // Galleries clone
            foreach ($originalProduct->galleries as $gallery) {
                $newPath = FileUploadHelper::copyFile($gallery->image, 'products/galleries');
                $newProduct->galleries()->create(['image' => $newPath]);
            }

            // Variation product
            if ($originalProduct->type === 'variation') {
                foreach ($originalProduct->variations as $variation) {
                    $variationData = $variation->toArray();
                    unset(
                        $variationData['id'],
                        $variationData['product_id'],
                        $variationData['created_at'],
                        $variationData['updated_at'],
                        $variationData['attributes'],
                        $variationData['stocks'],
                        $variationData['galleries'],
                        $variationData['combination_hash'], // unset করুন
                    );

                    if (!empty($variation->image)) {
                        $variationData['image'] = FileUploadHelper::copyFile(
                            $variation->image,
                            'products/variations'
                        );
                    }

                    // Attributes থেকে hash এর জন্য data তৈরি করুন
                    $attributesForHash = $variation->attributes->map(fn($attr) => [
                        'attribute_group_id' => $attr->attribute_group_id,
                        'attribute_value_id' => $attr->attribute_value_id,
                    ])->toArray();

                    // নতুন product id দিয়ে নতুন hash generate করুন
                    $variationData['combination_hash'] = $this->generateCombinationHash(
                        $newProduct->id,
                        $attributesForHash
                    );

                    // Clone variation sku unique
                    if (!empty($variationData['sku'])) {
                        $baseSku = $variation->sku . '_copy';
                        $variationData['sku'] = $baseSku;
                        $counter = 2;
                        while (ProductVariation::where('sku', $variationData['sku'])
                            ->whereHas('product', fn($q) => $q->where('company_id', $newProduct->company_id))
                            ->exists()
                        ) {
                            $variationData['sku'] = $baseSku . '_' . $counter;
                            $counter++;
                        }
                    }

                    $variationData['combination_hash'] = $this->generateCombinationHash(
                        $newProduct->id,
                        $attributesForHash
                    );

                    $newVariation = $newProduct->variations()->create($variationData);
                    // Clone variation attributes
                    foreach ($variation->attributes as $attribute) {
                        $newVariation->attributes()->create([
                            'attribute_group_id' => $attribute->attribute_group_id,
                            'attribute_value_id' => $attribute->attribute_value_id,
                        ]);
                    }

                    // Clone variation stocks
                    foreach ($variation->stocks as $stock) {
                        $newVariation->stocks()->create([
                            'warehouse_id' => $stock->warehouse_id,
                            'bin_id'       => $stock->bin_id,
                            'quantity'     => $stock->quantity,
                            'company_id'   => $stock->company_id,
                        ]);
                    }

                    // Clone variation galleries
                    foreach ($variation->galleries as $gallery) {
                        $newPath = FileUploadHelper::copyFile(
                            $gallery->image,
                            'products/variations/galleries'
                        );
                        $newVariation->galleries()->create(['image' => $newPath]);
                    }
                }
            }

            DB::commit();

            LogHelper::created('product', $newProduct->id, $newProduct->company_id);

            return $newProduct->load([
                'brand',
                'galleries',
                'variations.attributes.attributeGroup',
                'variations.attributes.attributeValue',
                'variations.stocks.warehouse',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product clone failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to clone product: ' . $e->getMessage());
        }
    }
    /**
     * Create a single product (no variations)
     */
    private function createSingleProduct(array $data): Product
    {
        // Extract warehouse info
        $manageStock =  $data['manage_stock'] == 1 ? true : false;
        $warehouseInfo = $data['warehouse_info'] ?? [];
        unset($data['warehouse_info']);



        // Calculate total stock from warehouse_info
        if (!$manageStock) {
            $warehouseInfo = collect($warehouseInfo)->map(function ($wh) {
                $wh['quantity'] = 0;
                return $wh;
            })->toArray();
        }

        $totalStock = $manageStock ? collect($warehouseInfo)->sum('quantity') : 0;

        // Set stock-related fields for single product
        $data['stock_quantity'] = $totalStock;
        $data['available_stock'] = $totalStock;
        $data['stock_status'] = $manageStock
            ? ($totalStock > 0 ? 'in_stock' : 'out_of_stock')
            : 'in_stock';
        $data['warehouse_info'] = $warehouseInfo;
        // Create the product
        $product = Product::create($data);

        // Create initial stock ledger entries for each warehouse
        if ($manageStock && !empty($warehouseInfo)) {
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
        $data['purchase_price'] = null;
        $data['discount_type'] = null;
        $data['discount'] = 0;
        $data['warehouse_info'] = null; // Don't use JSON for variations

        // Calculate total stock (will be updated after creating variations)
        $data['stock_quantity'] = 0;
        $data['available_stock'] = 0;
        $data['stock_status'] = 'out_of_stock';
        $manageStock =  $data['manage_stock'] == 1 ? true : false;

        // Create the product
        $product = Product::create($data);

        $totalStock = 0;

        // Create each variation
        foreach ($variations as $variationData) {
            $attributes = $variationData['attributes'] ?? [];
            $warehouseInfo = $variationData['warehouse_info'] ?? [];
            if (!$manageStock) {
                $warehouseInfo = collect($warehouseInfo)->map(function ($wh) {
                    $wh['quantity'] = 0;
                    return $wh;
                })->toArray();
            }
            $galleryImages = $variationData['gallery_images'] ?? [];
            unset($variationData['gallery_images']);

            if (!empty($variationData['image'])) {
                $variationData['image'] = FileUploadHelper::uploadImage(
                    $variationData['image'],
                    'products/variation',

                );
            }

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
                'purchase_price' => $variationData['purchase_price'],
                'discount_type' => $variationData['discount_type'] ?? 'flat',
                'discount' => $variationData['discount'] ?? 0,
                'stock_quantity' => $variationStock,
                'available_stock' => $variationStock,
                'stock_status' => $manageStock
                    ? ($variationStock > 0 ? 'in_stock' : 'out_of_stock')
                    : 'in_stock',
                'combination_hash' => $combinationHash,
            ]);
            if (!empty($galleryImages)) {
                $this->createVariationGalleries($variation->id, $galleryImages);
            }
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
                        'quantity' => $whStock['quantity'] ?? 0
                    ]);

                    if (($whStock['quantity'] ?? 0) > 0) {
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
            $previousAssignedTo = $product->assigned_to;
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
            $newAssignedTo = $product->assigned_to;

            if ($previousAssignedTo != $newAssignedTo) {
                $actor = auth()->user();
                $isActorCompanySuperAdmin = $actor && !is_null($actor->company_id) && is_null($actor->role);

                $companySuperAdmins = collect();
                if (!is_null($product->company_id) && !$isActorCompanySuperAdmin) {
                    $companySuperAdmins = NotificationRecipientResolver::companySuperAdmin($product->company_id);
                }

                // Purano user remove
                if (!empty($previousAssignedTo)) {
                    $removedUser = User::find($previousAssignedTo);
                    if ($removedUser) {
                        $recipients = collect([$removedUser])->concat($companySuperAdmins);

                        NotificationService::notify(
                            $recipients,
                            new ProductUnassignedNotification(
                                $product->id,
                                $product->title,
                                $product->company_id,
                                $removedUser->id,
                            )
                        );
                    }
                }

                // Notun user assign
                if (!empty($newAssignedTo)) {
                    $assignedUser = User::find($newAssignedTo);
                    if ($assignedUser) {
                        $recipients = collect([$assignedUser])->concat($companySuperAdmins);

                        NotificationService::notify(
                            $recipients,
                            new ProductAssignedNotification(
                                $product->id,
                                $product->title,
                                $product->company_id,
                                $assignedUser->id,
                            )
                        );
                    }
                }
            }
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

        $manageStock = ($data['manage_stock'] ?? $product->manage_stock) == 1 ? true : false;

        $warehouseInfo = $data['warehouse_info'] ?? [];
        unset($data['warehouse_info']);
        if (!$manageStock) {
            $warehouseInfo = collect($warehouseInfo)->map(function ($wh) {
                $wh['quantity'] = 0;
                return $wh;
            })->toArray();
        }
        $totalStock = $manageStock ? collect($warehouseInfo)->sum('quantity') : 0;   // ← পরিবর্তিত
        $data['stock_quantity'] = $totalStock;
        $data['available_stock'] = $totalStock;
        $data['stock_status'] = $manageStock
            ? ($totalStock > 0 ? 'in_stock' : 'out_of_stock')
            : 'in_stock';
        $data['warehouse_info'] = $warehouseInfo;


        // ── Stock Ledger Update ──────────────────────────────────────
        if ($manageStock && !empty($warehouseInfo)) {
            foreach ($warehouseInfo as $warehouseStock) {
                $newQty = (int) ($warehouseStock['quantity'] ?? 0);
                $warehouseId = $warehouseStock['warehouse_id'];
                $binId = $warehouseStock['bin_id'] ?? null;

                // Get previous quantity from last ledger entry for this warehouse
                $lastLedger = ProductStockLedger::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouseId)
                    ->latest()
                    ->first();

                $qtyBefore = $lastLedger ? $lastLedger->quantity_after : 0;

                // Only create ledger entry if quantity actually changed
                if ($qtyBefore === $newQty) {
                    continue;
                }

                ProductStockLedger::create([
                    'product_id'       => $product->id,
                    'variation_id'     => null,
                    'warehouse_id'     => $warehouseId,
                    'bin_id'           => $binId,
                    'batch_number'     => null,
                    'serial_numbers'   => null,
                    'transaction_type' => 'adjustment',
                    'reference_type'   => 'Product',
                    'reference_id'     => $product->id,
                    'quantity_before'  => $qtyBefore,
                    'quantity_change'  => $newQty - $qtyBefore, // negative if reduced
                    'quantity_after'   => $newQty,
                    'notes'            => 'Stock adjusted on product update',
                    'created_by'       => Auth::id(),
                ]);
            }
        }
    }
    /**
     * Update variation product
     */
    private function updateVariationProduct(Product $product, array $data): void
    {
        // Get existing variations with their attributes and galleries
        $existingVariations = $product->variations()
            ->with(['attributes', 'galleries']) // ✅ Eager load galleries here
            ->get()
            ->keyBy('id');

        $newVariations = $data['variations'] ?? [];
        $processedVariationIds = [];
        $manageStock = ($data['manage_stock'] ?? $product->manage_stock) == 1 ? true : false;
        foreach ($newVariations as $variationData) {
            $attributes = $variationData['attributes'] ?? [];

            // ✅ Extract gallery data
            $galleryImages = $variationData['gallery_images'] ?? [];
            $deleteGalleryIds = $variationData['deleted_gallery_ids'] ?? [];

            // Generate combination hash for this variation
            $combinationHash = $this->generateCombinationHash($product->id, $attributes);

            if (!empty($variationData['image'])) {
                $variationData['image'] = FileUploadHelper::uploadImage(
                    $variationData['image'],
                    'products/variation',

                );
            }
            $warehouseInfo = $variationData['warehouse_info'] ?? [];
            if (!$manageStock) {
                $warehouseInfo = collect($warehouseInfo)->map(function ($wh) {
                    $wh['quantity'] = 0;
                    return $wh;
                })->toArray();
            }
            $stockQty = $manageStock ? ($variationData['stock_quantity'] ?? 0) : 0;   // ← নতুন লাইন

            // Check if this combination already exists (by hash, not by ID)
            $existingVariation = ProductVariation::where('product_id', $product->id)
                ->where('combination_hash', $combinationHash)
                ->first();

            if ($existingVariation) {

                // UPDATE existing variation
                $existingVariation->update([
                    'sku' => $variationData['sku'] ?? null,
                    'image' => $variationData['image'] ?? $existingVariation->image,
                    'regular_price' => $variationData['regular_price'],
                    'purchase_price' => $variationData['purchase_price'],
                    'discount_type' => $variationData['discount_type'] ?? 'flat',
                    'discount' => $variationData['discount'] ?? 0,
                    'stock_quantity' => $stockQty,
                    'available_stock' => $stockQty,
                    'stock_status' => $manageStock
                        ? $this->determineStockStatus($stockQty)
                        : 'in_stock',
                ]);

                // Update warehouse stocks if provided
                if (isset($variationData['warehouse_info'])) {
                    $this->updateVariationWarehouseStocks(
                        $product,
                        $existingVariation,
                        $warehouseInfo
                    );
                }

                // ✅ 1. Delete requested existing galleries
                if (!empty($deleteGalleryIds)) {
                    $this->deleteVariationGalleries($deleteGalleryIds);
                }


                // ✅ 2. Add new galleries for existing variation
                if (!empty($galleryImages)) {
                    $this->createVariationGalleries($existingVariation->id, $galleryImages);
                }

                $processedVariationIds[] = $existingVariation->id;

                Log::info('Variation updated', [
                    'variation_id' => $existingVariation->id,
                    'combination_hash' => $combinationHash
                ]);
            } else {
                // CREATE new variation (this combination doesn't exist yet)
                $newVariation = ProductVariation::create([
                    'product_id' => $product->id,
                    'sku' => $variationData['sku'] ?? null,
                    'image' => $variationData['image'] ?? null,
                    'regular_price' => $variationData['regular_price'],
                    'discount_type' => $variationData['discount_type'] ?? 'flat',
                    'discount' => $variationData['discount'] ?? 0,
                    'stock_quantity' => $stockQty,
                    'available_stock' => $stockQty,
                    'stock_status' => $manageStock
                        ? $this->determineStockStatus($stockQty)
                        : 'in_stock',
                    'combination_hash' => $combinationHash,
                ]);

                // Create variation attributes
                foreach ($attributes as $attribute) {
                    ProductVariationAttribute::create([
                        'product_variation_id' => $newVariation->id,
                        'attribute_group_id' => $attribute['attribute_group_id'],
                        'attribute_value_id' => $attribute['attribute_value_id'],
                    ]);
                }

                // Create warehouse stocks if provided
                if (isset($variationData['warehouse_info'])) {
                    $this->createVariationWarehouseStocks(
                        $product,
                        $newVariation,
                        $warehouseInfo   
                    );
                }


                // ✅ 3. Create galleries for the new variation
                if (!empty($galleryImages)) {
                    $this->createVariationGalleries($newVariation->id, $galleryImages);
                }

                $processedVariationIds[] = $newVariation->id;

                Log::info('New variation created', [
                    'variation_id' => $newVariation->id,
                    'combination_hash' => $combinationHash
                ]);
            }
        }

        // Delete variations that are no longer in the request
        $variationsToDelete = $existingVariations->filter(function ($variation) use ($processedVariationIds) {
            return !in_array($variation->id, $processedVariationIds);
        });

        foreach ($variationsToDelete as $variation) {
            // Create reversal ledger entries for deleted variations
            $stocks = ProductVariationStock::where('product_variation_id', $variation->id)->get();

            foreach ($stocks as $stock) {
                if ($stock->quantity > 0) {
                    ProductVariationStockLedger::create([
                        'product_id' => $product->id,
                        'variation_id' => $variation->id,
                        'warehouse_id' => $stock->warehouse_id,
                        'bin_id' => $stock->bin_id,
                        'batch_number' => null,
                        'serial_numbers' => null,
                        'transaction_type' => 'adjustment',
                        'reference_type' => 'VariationDeleted',
                        'reference_id' => $variation->id,
                        'quantity_before' => $stock->quantity,
                        'quantity_change' => -$stock->quantity,
                        'quantity_after' => 0,
                        'notes' => "Variation deleted: " . $this->getVariationDisplayName($variation->id),
                        'created_by' => Auth::id(),
                    ]);
                }
            }



            // Delete related records
            $variation->galleries()->delete(); // ✅ Delete galleries from database
            $variation->attributes()->delete();
            $variation->stocks()->delete();
            $variation->delete();

            Log::info('Variation deleted', ['variation_id' => $variation->id]);
        }

        // Recalculate total stock from all variations
        $this->recalculateTotalStockFromVariations($product);
    }


    /**
     * Generate unique hash for attribute combination
     */
    // private function generateCombinationHash(int $productId, array $attributes): string
    // {
    //     // Sort attributes by group_id for consistent hashing
    //     $sorted = collect($attributes)->sortBy('attribute_group_id')->values()->all();
    //     $hashData = $productId . '-' . json_encode($sorted);
    //     return hash('sha256', $hashData);
    // }
    private function generateCombinationHash(int $productId, array $attributes): string
    {
        $sorted = collect($attributes)
            ->map(function ($attr) {
                return [
                    'attribute_group_id' => (int) $attr['attribute_group_id'],
                    'attribute_value_id' => (int) $attr['attribute_value_id'],
                ];
            })
            ->sortBy('attribute_group_id')
            ->values()
            ->all();

        return hash('sha256', $productId . '-' . json_encode($sorted));
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

            );

            Gallery::create([
                'product_id' => $productId,
                'image' => $imagePath
            ]);
        }
    }
    private function createVariationGalleries(int $variationId, array $images, array $titles = []): void
    {
        foreach ($images as $index => $image) {
            $imagePath = FileUploadHelper::uploadImage(
                $image,
                'products/galleries',

            );

            VariationGallery::create([
                'variation_id' => $variationId,
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
    private function deleteVariationGalleries(array $galleryIds): void
    {
        $galleries = VariationGallery::whereIn('id', $galleryIds)->get();

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
            if (!$product->manage_stock) {
                throw ApiException::badRequest('Stock tracking is disabled for this product');
            }
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

        if (!$variation || (int)$variation->product_id !== (int)$product->id) {
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


        if (!$variation || (int)$variation->product_id !== (int)$product->id) {
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

    // PRIVATE HELPER METHODS

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
            if (!$product->manage_stock) {
                return $product;
            }
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

        if (!$variation || (int)$variation->product_id !== (int)$product->id) {
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
            if (!$product->manage_stock) {
                return $product;
            }

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

        if (!$variation || (int)$variation->product_id !== (int)$product->id) {
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

    /**
     * Create warehouse stocks for new variations
     */
    private function createVariationWarehouseStocks(Product $product, ProductVariation $variation, array $warehouseInfo): void
    {
        foreach ($warehouseInfo as $warehouse) {
            $warehouseId = $warehouse['warehouse_id'];
            $binId = $warehouse['bin_id'] ?? null;
            $quantity = $warehouse['quantity'];

            // Create stock record
            ProductVariationStock::create([
                'product_variation_id' => $variation->id,
                'warehouse_id' => $warehouseId,
                'bin_id' => $binId,
                'quantity' => $quantity,
            ]);

            // Create ledger entry
            ProductVariationStockLedger::create([
                'product_id' => $product->id,
                'variation_id' => $variation->id,
                'warehouse_id' => $warehouseId,
                'bin_id' => $binId,
                'batch_number' => null,
                'serial_numbers' => null,
                'transaction_type' => 'initial_stock',
                'reference_type' => 'ProductCreation',
                'reference_id' => $product->id,
                'quantity_before' => 0,
                'quantity_change' => $quantity,
                'quantity_after' => $quantity,
                'notes' => "Initial stock for variation: " . $this->getVariationDisplayName($variation->id),
                'created_by' => Auth::id(),
            ]);
        }
    }
    /**
     * Update variation warehouse stocks with proper ledger tracking
     */
    private function updateVariationWarehouseStocks(Product $product, ProductVariation $variation, array $warehouseInfo): void
    {
        foreach ($warehouseInfo as $warehouse) {
            $warehouseId = $warehouse['warehouse_id'];
            $binId = $warehouse['bin_id'] ?? null;
            $newQuantity = $warehouse['quantity'];

            // Get existing stock record
            $existingStock = ProductVariationStock::where('product_variation_id', $variation->id)
                ->where('warehouse_id', $warehouseId)
                ->where('bin_id', $binId)
                ->first();

            if ($existingStock) {
                // Calculate the difference
                $oldQuantity = $existingStock->quantity;
                $difference = $newQuantity - $oldQuantity;

                if ($difference != 0) {
                    // Update the stock record
                    $existingStock->update(['quantity' => $newQuantity]);

                    // Create ledger entry
                    ProductVariationStockLedger::create([
                        'product_id' => $product->id,
                        'variation_id' => $variation->id,
                        'warehouse_id' => $warehouseId,
                        'bin_id' => $binId,
                        'batch_number' => null,
                        'serial_numbers' => null,
                        'transaction_type' => 'adjustment',
                        'reference_type' => 'ProductUpdate',
                        'reference_id' => $product->id,
                        'quantity_before' => $oldQuantity,
                        'quantity_change' => $difference,
                        'quantity_after' => $newQuantity,
                        'notes' => "Stock adjusted during product update - Variation: " . $this->getVariationDisplayName($variation->id),
                        'created_by' => Auth::id(),
                    ]);

                    Log::info('Variation stock updated', [
                        'variation_id' => $variation->id,
                        'warehouse_id' => $warehouseId,
                        'old_quantity' => $oldQuantity,
                        'new_quantity' => $newQuantity,
                        'difference' => $difference
                    ]);
                }
            } else {
                // Create new stock record
                ProductVariationStock::create([
                    'product_variation_id' => $variation->id,
                    'warehouse_id' => $warehouseId,
                    'bin_id' => $binId,
                    'quantity' => $newQuantity,
                ]);

                // Create ledger entry
                ProductVariationStockLedger::create([
                    'product_id' => $product->id,
                    'variation_id' => $variation->id,
                    'warehouse_id' => $warehouseId,
                    'bin_id' => $binId,
                    'batch_number' => null,
                    'serial_numbers' => null,
                    'transaction_type' => 'adjustment',
                    'reference_type' => 'ProductUpdate',
                    'reference_id' => $product->id,
                    'quantity_before' => 0,
                    'quantity_change' => $newQuantity,
                    'quantity_after' => $newQuantity,
                    'notes' => "Initial stock added during product update - Variation: " . $this->getVariationDisplayName($variation->id),
                    'created_by' => Auth::id(),
                ]);

                Log::info('New variation stock created', [
                    'variation_id' => $variation->id,
                    'warehouse_id' => $warehouseId,
                    'quantity' => $newQuantity
                ]);
            }

            // Update variation's total stock quantities
            $totalStock = ProductVariationStock::where('product_variation_id', $variation->id)
                ->sum('quantity');

            $variation->update([
                'stock_quantity' => $totalStock,
                'available_stock' => $totalStock,
                'stock_status' => $this->determineStockStatus($totalStock),
            ]);
        }
    }
    /**
     * Determine stock status based on quantity
     */
    private function determineStockStatus(int $quantity): string
    {
        if ($quantity <= 0) {
            return 'out_of_stock';
        } elseif ($quantity <= 10) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }
}
