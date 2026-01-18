<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\Banner;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BannerService
{
    /**
     * Get all banner with optional pagination
     */
    public function getAllBanners(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Banner::query();
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['title']) && $filters['title'] !== '') {
                $query->where('title', 'like', "%{$filters['title']}%");
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching banners: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch banners');
        }
    }

    /**
     * Get banner by ID
     */
    public function getBannerById(int $id): Banner
    {
        $banner = Banner::find($id);

        if (!$banner) {
            throw ApiException::notFound('Banner');
        }

        return $banner;
    }

    /**
     * Create a new banner
     */
    public function createBanner(array $data): Banner
    {
        DB::beginTransaction();

        try {
            // Handle image upload
            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::uploadImage(
                    $data['image'],
                    'banners/images',
                    'public',
                    2048
                );
            }

            $banner = Banner::create($data);
            LogHelper::created('banner', $banner->id, $banner->company_id);

            DB::commit();

            Log::info('Banner created successfully', ['banner_id' => $banner->id]);

            return $banner;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['image'])) {
                FileUploadHelper::delete($data['image']);
            }

            Log::error('Banner creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create banner');
        }
    }

    /**
     * Update banner
     */
    public function updateBanner(int $id, array $data): Banner
    {
        DB::beginTransaction();

        try {
            $banner = $this->getBannerById($id);

            // Handle image upload
            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::replace(
                    $data['image'],
                    $banner->image,
                    'banners/images'
                );
            }

            $banner->update($data);
            LogHelper::updated('banner', $banner->id, $banner->company_id);

            DB::commit();

            Log::info('Banner updated successfully', ['banner_id' => $banner->id]);

            return $banner;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['image'])) {
                FileUploadHelper::delete($data['image']);
            }

            Log::error('Banner update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update banner');
        }
    }

    /**
     * Delete banner (soft delete)
     */
    public function deleteBanner(int $id): bool
    {
        DB::beginTransaction();
        try {
            $banner = $this->getBannerById($id);
            $banner->delete();
            LogHelper::deleted('banner', $banner->id, $banner->company_id);
            DB::commit();
            Log::info('Banner deleted successfully', ['banner_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Banner deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete banner');
        }
    }

    /**
     * Restore soft deleted banner
     */
    public function restoreBanner(int $id): Banner
    {
        DB::beginTransaction();
        try {
            $banner = Banner::withTrashed()->find($id);

            if (!$banner) {
                throw ApiException::notFound('Banner');
            }

            $banner->restore();
            LogHelper::restored('banner', $banner->id, $banner->company_id);

            Log::info('Banner restored successfully', ['banner_id' => $id]);
            DB::commit();
            return $banner;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Banner restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore banner');
        }
    }

    /**
     * Permanently delete banner
     */
    public function forceDeleteBanner(int $id): bool
    {
        DB::beginTransaction();

        try {
            $banner = Banner::withTrashed()->find($id);

            if (!$banner) {
                throw ApiException::notFound('Banner');
            }

            // Delete logo
            FileUploadHelper::delete($banner->image);

            $banner->forceDelete();
            LogHelper::forceDeleted('banner', $banner->id, $banner->company_id);

            DB::commit();

            Log::info('Banner permanently deleted', ['banner_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Banner permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete banner');
        }
    }

    /**
     * Toggle banner status
     */
    public function toggleStatus(int $id): Banner
    {
        DB::beginTransaction();
        try {
            $banner = $this->getBannerById($id);

            $newStatus = $banner->status == 1 ? 0 : 1;
            $banner->update(['status' => $newStatus]);

            LogHelper::statusChanged('banner', $banner->id, $banner->company_id);
            DB::commit();
            Log::info('Banner status toggled', ['banner_id' => $id, 'new_status' => $newStatus]);

            return $banner;

        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Banner status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle banner status');
        }
    }
}
