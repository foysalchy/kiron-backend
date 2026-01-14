<?php

namespace App\Services;

use App\Models\Brand;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BrandService
{
    /**
     * Get all brands with optional pagination
     */
    public function getAllBrands(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Brand::query();
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['search'])) {
                $query->where('name', 'like', "%{$filters['search']}%");
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching brands: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch brands');
        }
    }

    /**
     * Get brand by ID
     */
    public function getBrandById(int $id): Brand
    {
        $brand = Brand::find($id);

        if (!$brand) {
            throw ApiException::notFound('Brand');
        }

        return $brand;
    }

    /**
     * Create a new brand
     */
    public function createBrand(array $data): Brand
    {
        DB::beginTransaction();

        try {
            // Handle logo upload
            if (isset($data['logo'])) {
                $data['logo'] = FileUploadHelper::uploadImage(
                    $data['logo'],
                    'brands/logos',
                    'public',
                    2048
                );
            }

            $brand = Brand::create($data);
            LogHelper::created('brand', $brand->id, $brand->company_id);

            DB::commit();

            Log::info('Brand created successfully', ['brand_id' => $brand->id]);

            return $brand;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['logo'])) {
                FileUploadHelper::delete($data['logo']);
            }

            Log::error('Brand creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create brand');
        }
    }

    /**
     * Update brand
     */
    public function updateBrand(int $id, array $data): Brand
    {
        DB::beginTransaction();

        try {
            $brand = $this->getBrandById($id);

            // Handle logo upload
            if (isset($data['logo'])) {
                $data['logo'] = FileUploadHelper::replace(
                    $data['logo'],
                    $brand->logo,
                    'brands/logos'
                );
            }

            $brand->update($data);
            LogHelper::updated('brand', $brand->id, $brand->company_id);

            DB::commit();

            Log::info('Brand updated successfully', ['brand_id' => $brand->id]);

            return $brand;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['logo'])) {
                FileUploadHelper::delete($data['logo']);
            }

            Log::error('Brand update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update brand');
        }
    }

    /**
     * Delete brand (soft delete)
     */
    public function deleteBrand(int $id): bool
    {
        try {
            $brand = $this->getBrandById($id);
            $brand->delete();
            LogHelper::deleted('brand', $brand->id, $brand->company_id);

            Log::info('Brand deleted successfully', ['brand_id' => $id]);

            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Brand deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete brand');
        }
    }

    /**
     * Restore soft deleted brand
     */
    public function restoreBrand(int $id): Brand
    {
        try {
            $brand = Brand::withTrashed()->find($id);

            if (!$brand) {
                throw ApiException::notFound('Brand');
            }

            $brand->restore();
            LogHelper::restored('brand', $brand->id, $brand->company_id);

            Log::info('Brand restored successfully', ['brand_id' => $id]);

            return $brand;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Brand restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore brand');
        }
    }

    /**
     * Permanently delete brand
     */
    public function forceDeleteBrand(int $id): bool
    {
        DB::beginTransaction();

        try {
            $brand = Brand::withTrashed()->find($id);

            if (!$brand) {
                throw ApiException::notFound('Brand');
            }

            // Delete logo
            FileUploadHelper::delete($brand->logo);

            $brand->forceDelete();
            LogHelper::forceDeleted('brand', $brand->id, $brand->company_id);

            DB::commit();

            Log::info('Brand permanently deleted', ['brand_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Brand permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete brand');
        }
    }

    /**
     * Toggle brand status
     */
    public function toggleStatus(int $id): Brand
    {
        try {
            $brand = $this->getBrandById($id);
            $brand->update(['status' => !$brand->status]);
            LogHelper::statusChanged('brand', $brand->id, $brand->company_id);
            Log::info('Brand status toggled', ['brand_id' => $id]);

            return $brand;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Brand status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle brand status');
        }
    }
}
