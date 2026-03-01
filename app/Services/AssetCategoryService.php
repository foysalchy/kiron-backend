<?php
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\AssetCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class AssetCategoryService
{
    /**
     * Get all asset categories with optional pagination and filters
     */
    public function getAllCategories(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = AssetCategory::query();

            // Filter by Status (including Trashed)
            if (isset($filters['status'])) {
                if ($filters['status'] === 'Trashed' || $filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            // Search by name
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where('name', 'like', "%{$search}%");
            }

            // Sorting
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching asset categories: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch asset categories');
        }
    }
    /**
     * Get asset category by ID
     */
    public function getCategoryById(int $id): AssetCategory
    {
        $category = AssetCategory::find($id);
        if (!$category) {
            throw ApiException::notFound('asset category');
        }
        return $category;
    }

    /**
     * Create a new asset category
     */
    public function createCategory(array $data): AssetCategory
    {
        DB::beginTransaction();
        try {
            $category = AssetCategory::create($data);

            LogHelper::created('asset_category', $category->id, $category->company_id, $category->name);
            DB::commit();

            Log::info('Asset category created successfully', ['category_id' => $category->id]);

            return $category;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset category creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create asset category');
        }
    }

    /**
     * Update asset category
     */
    public function updateCategory(int $id, array $data): AssetCategory
    {
        DB::beginTransaction();
        try {
            $category = $this->getCategoryById($id);

            $category->update($data);

            LogHelper::updated('asset_category', $category->id, $category->company_id, $category->name);
            DB::commit();

            Log::info('Asset category updated successfully', ['category_id' => $category->id]);

            return $category->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset category update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update asset category');
        }
    }

    /**
     * Delete asset category (soft delete)
     */
    public function deleteCategory(int $id): bool
    {
        DB::beginTransaction();
        try {
            $category = $this->getCategoryById($id);

            $category->delete();

            LogHelper::deleted('asset_category', $category->id, $category->company_id, $category->name);
            DB::commit();

            Log::info('Asset category deleted successfully', ['category_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset category deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete asset category');
        }
    }

    /**
     * Restore soft deleted asset category
     */
    public function restoreCategory(int $id): AssetCategory
    {
        DB::beginTransaction();
        try {
            $category = AssetCategory::withTrashed()->find($id);
            if (!$category) {
                throw ApiException::notFound('Asset category');
            }

            $category->restore();

            LogHelper::restored('asset_category', $category->id, $category->company_id, $category->name);
            DB::commit();
            Log::info('Asset category restored successfully', ['category_id' => $id]);

            return $category;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset category restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore asset category');
        }
    }

    /**
     * Permanently delete an asset category
     */
    public function forceDeleteCategory(int $id): bool
    {
        DB::beginTransaction();
        try {
            $category = AssetCategory::withTrashed()->find($id);
            if (!$category) {
                throw ApiException::notFound('Asset category');
            }

            $category->forceDelete();

            LogHelper::forceDeleted('asset_category', $id, $category->company_id, $category->name);
            DB::commit();
            Log::info('Asset category permanently deleted successfully', ['category_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset category permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete asset category');
        }
    }

    /**
     * Toggle asset category status (Active/Inactive)
     */
    public function toggleStatus(int $id): AssetCategory
    {
        DB::beginTransaction();
        try {
            $category = $this->getCategoryById($id);

            $currentStatus = Status::from($category->status);

            // Toggle logic
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;

            $category->update([
                'status' => $newStatus->value
            ]);

            LogHelper::statusChanged('asset_category', $category->id, $category->company_id, $category->name .' new status '.$newStatus->label());
            DB::commit();

            Log::info('Asset category status toggled', ['category_id' => $id, 'new_status' => $newStatus->label()]);

            return $category;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset category status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle asset category status');
        }
    }
}
