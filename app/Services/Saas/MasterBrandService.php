<?php

namespace App\Services\Saas;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\MasterBrand;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MasterBrandService
{
    /**
     * Get all brands with optional pagination
     */
    public function getAllBrands(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            // SaaS ল্যান্ডিং পেজের জন্য গ্লোবাল ডাটা হলে withoutCompanyScope ব্যবহার করতে পারেন
            $query = MasterBrand::query();

            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching master brands: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch brands');
        }
    }

    /**
     * Get brand by ID
     */
    public function getBrandById(int $id): MasterBrand
    {
        $brand = MasterBrand::find($id);
        if (!$brand) {
            throw ApiException::notFound('Brand');
        }
        return $brand;
    }

    /**
     * Create a new master brand
     */
    public function createBrand(array $data): MasterBrand
    {
        DB::beginTransaction();
        try {
            if (isset($data['logo'])) {
                $data['logo'] = FileUploadHelper::uploadImage(
                    $data['logo'],
                    'saas/brands',

                );
            }

            $brand = MasterBrand::create($data);

            LogHelper::created('master_brand', $brand->id, 0, $brand->name);
            DB::commit();
            Log::info('Master Brand created successfully', ['brand_id' => $brand->id]);

            return $brand;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Master Brand creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create brand');
        }
    }

    /**
     * Update brand
     */
    public function updateBrand(int $id, array $data): MasterBrand
    {
        DB::beginTransaction();
        try {
            $brand = $this->getBrandById($id);

            // Handle logo upload
            if (isset($data['logo'])) {
                $data['logo'] = FileUploadHelper::replace(
                    $data['logo'],
                    $brand->logo,
                    'saas/brands'
                );
            }

            $brand->update($data);

            LogHelper::updated('master_brand', $brand->id, 0, $brand->name);
            DB::commit();
            Log::info('Master Brand Updated Successfully', ['brand_id' => $brand->id]);

            return $brand->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Master Brand update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update brand');
        }
    }

    /**
     * Delete brand (soft delete)
     */
    public function deleteBrand(int $id): bool
    {
        DB::beginTransaction();
        try {
            $brand = $this->getBrandById($id);
            $brand->delete();

            LogHelper::deleted('master_brand', $brand->id, 0, $brand->name);
            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Master Brand deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete brand');
        }
    }

    /**
     * Toggle brand status (Active/Inactive)
     */
    public function toggleStatus(int $id): MasterBrand
    {
        DB::beginTransaction();
        try {
            $brand = $this->getBrandById($id);

            $newStatus = ($brand->status == Status::Active->value)
                ? Status::Inactive->value
                : Status::Active->value;

            $brand->update(['status' => $newStatus]);

            LogHelper::statusChanged('master_brand', $brand->id, 0, $brand->name . ' to ' . $newStatus);
            DB::commit();
            return $brand;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Master Brand status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}
