<?php

namespace App\Services;

use App\Models\MiniCategory;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class MiniCategoryService
{
    public function getAllMiniCategories(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = MiniCategory::with(['company', 'subCategory.megaCategory']);

            if (isset($filters['company_id'])) {
                $query->where('company_id', $filters['company_id']);
            }

            if (isset($filters['sub_category_id'])) {
                $query->where('sub_category_id', $filters['sub_category_id']);
            }

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
            Log::error('Error fetching mini categories: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch mini categories');
        }
    }

    public function getMiniCategoryById(int $id): MiniCategory
    {
        $category = MiniCategory::with(['company', 'subCategory.megaCategory'])->find($id);

        if (!$category) {
            throw ApiException::notFound('Mini Category');
        }

        return $category;
    }

    public function createMiniCategory(array $data): MiniCategory
    {
        DB::beginTransaction();

        try {
            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::uploadImage(
                    $data['image'],
                    'categories/mini',
                    'public',
                    2048
                );
            }

            $category = MiniCategory::create($data);

            DB::commit();

            Log::info('Mini category created successfully', ['id' => $category->id]);

            return $category->load(['company', 'subCategory.megaCategory']);
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['image'])) {
                FileUploadHelper::delete($data['image']);
            }

            Log::error('Mini category creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create mini category');
        }
    }

    public function updateMiniCategory(int $id, array $data): MiniCategory
    {
        DB::beginTransaction();

        try {
            $category = $this->getMiniCategoryById($id);

            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::replace(
                    $data['image'],
                    $category->image,
                    'categories/mini'
                );
            }

            $category->update($data);

            DB::commit();

            Log::info('Mini category updated successfully', ['id' => $category->id]);

            return $category->fresh(['company', 'subCategory.megaCategory']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['image'])) {
                FileUploadHelper::delete($data['image']);
            }

            Log::error('Mini category update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update mini category');
        }
    }

    public function deleteMiniCategory(int $id): bool
    {
        try {
            $category = $this->getMiniCategoryById($id);
            $category->delete();

            Log::info('Mini category deleted successfully', ['id' => $id]);

            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Mini category deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete mini category');
        }
    }

    public function restoreMiniCategory(int $id): MiniCategory
    {
        try {
            $category = MiniCategory::withTrashed()->find($id);

            if (!$category) {
                throw ApiException::notFound('Mini Category');
            }

            $category->restore();

            Log::info('Mini category restored successfully', ['id' => $id]);

            return $category->load(['company', 'subCategory.megaCategory']);
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Mini category restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore mini category');
        }
    }

    public function forceDeleteMiniCategory(int $id): bool
    {
        DB::beginTransaction();

        try {
            $category = MiniCategory::withTrashed()->find($id);

            if (!$category) {
                throw ApiException::notFound('Mini Category');
            }

            FileUploadHelper::delete($category->image);

            $category->forceDelete();

            DB::commit();

            Log::info('Mini category permanently deleted', ['id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Mini category permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete mini category');
        }
    }

    public function toggleStatus(int $id): MiniCategory
    {
        try {
            $category = $this->getMiniCategoryById($id);
            $category->update(['status' => !$category->status]);

            Log::info('Mini category status toggled', ['id' => $id]);

            return $category->load(['company', 'subCategory.megaCategory']);
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Mini category status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }

    public function getBySubCategory(int $subId, int $companyId): Collection
    {
        return MiniCategory::byCompany($companyId)
            ->bySubCategory($subId)
            ->active()
            ->get();
    }

    
}
