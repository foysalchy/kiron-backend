<?php

namespace App\Services;

use App\Models\{Product, Gallery};
use App\Exceptions\ApiException;
use App\Helpers\{FileUploadHelper, LogHelper};
use Illuminate\Container\Attributes\Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class ProductService
{
    /**
     * Get all products with optional pagination
     */
    public function getAllProducts(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Product::with([ 'brand', 'galleries']);

            if (isset($filters['company_id'])) {
                $query->where('company_id', $filters['company_id']);
            }

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
    public function getAllCompanyProducts(array $filters = [],int $companyId, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Product::with([ 'brand', 'galleries'])->where('company_id',$companyId);

            if (isset($filters['company_id'])) {
                $query->where('company_id', $filters['company_id']);
            }

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
        $product = Product::with(['company', 'brand', 'galleries'])->find($id);

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

            // Extract gallery data
            $galleryImages = $data['gallery_images'] ?? [];
            unset($data['gallery_images']);

            // Create product
            $product = Product::create($data);

            // Upload and create galleries
            if (!empty($galleryImages)) {
                $this->createGalleries($product->id, $galleryImages);
            }

            DB::commit();

            Log::info('Product created successfully', ['product_id' => $product->id]);
            LogHelper::created('product', $product->id, $product->company_id);

            return $product->load(['company', 'brand', 'galleries']);
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['thumbnail'])) {
                FileUploadHelper::delete($data['thumbnail']);
            }

            Log::error('Product creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create product');
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
            unset($data['gallery_images']);

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

            return $product->fresh(['company', 'brand', 'galleries']);
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

            return $product->load(['company', 'brand', 'galleries']);
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

            return $product->load(['company', 'brand', 'galleries']);
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

   
}
