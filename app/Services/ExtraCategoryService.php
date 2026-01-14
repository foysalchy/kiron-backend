<?php

namespace App\Services;

use App\Models\ExtraCategory;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class ExtraCategoryService
{
    public function getAllExtraCategories(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = ExtraCategory::with(['miniCategory.subCategory.megaCategory']);

            if (isset($filters['company_id'])) {
                $query->where('company_id', $filters['company_id']);
            }

            if (isset($filters['mini_category_id'])) {
                $query->where('mini_category_id', $filters['mini_category_id']);
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
            Log::error('Error fetching extra categories: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch extra categories');
        }
    }
 

    public function getExtraCategoryById(int $id): ExtraCategory
    {
        $category = ExtraCategory::with(['miniCategory.subCategory.megaCategory'])->find($id);

        if (!$category) {
            throw ApiException::notFound('Extra Category');
        }

        return $category;
    }

    public function createExtraCategory(array $data): ExtraCategory
    {
        DB::beginTransaction();

        try {
            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::uploadImage(
                    $data['image'],
                    'categories/extra',
                    'public',
                    2048
                );
            }

            $category = ExtraCategory::create($data);
            LogHelper::created('extra_categories', $category->id, $category->company_id);

            DB::commit();

            Log::info('Extra category created successfully', ['id' => $category->id]);

            return $category->load(['miniCategory.subCategory.megaCategory']);
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['image'])) {
                FileUploadHelper::delete($data['image']);
            }

            Log::error('Extra category creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create extra category');
        }
    }

    public function updateExtraCategory(int $id, array $data): ExtraCategory
    {
        DB::beginTransaction();

        try {
            $category = $this->getExtraCategoryById($id);

            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::replace(
                    $data['image'],
                    $category->image,
                    'categories/extra'
                );
            }

            $category->update($data);
            LogHelper::updated('extra_categories', $category->id, $category->company_id);

            DB::commit();

            Log::info('Extra category updated successfully', ['id' => $category->id]);

            return $category->fresh([ 'miniCategory.subCategory.megaCategory']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['image'])) {
                FileUploadHelper::delete($data['image']);
            }

            Log::error('Extra category update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update extra category');
        }
    }

    public function deleteExtraCategory(int $id): bool
    {
        try {
            $category = $this->getExtraCategoryById($id);
            $category->delete();
            LogHelper::deleted('extra_categories', $category->id, $category->company_id);

            Log::info('Extra category deleted successfully', ['id' => $id]);

            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Extra category deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete extra category');
        }
    }

    public function restoreExtraCategory(int $id): ExtraCategory
    {
        try {
            $category = ExtraCategory::withTrashed()->find($id);

            if (!$category) {
                throw ApiException::notFound('Extra Category');
            }

            $category->restore();
            LogHelper::restored('extra_categories', $category->id, $category->company_id);

            Log::info('Extra category restored successfully', ['id' => $id]);

            return $category->load(['miniCategory.subCategory.megaCategory']);
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Extra category restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore extra category');
        }
    }

    public function forceDeleteExtraCategory(int $id): bool
    {
        DB::beginTransaction();

        try {
            $category = ExtraCategory::withTrashed()->find($id);

            if (!$category) {
                throw ApiException::notFound('Extra Category');
            }

            FileUploadHelper::delete($category->image);

            $category->forceDelete();
            LogHelper::forceDeleted('extra_categories', $category->id, $category->company_id);

            DB::commit();

            Log::info('Extra category permanently deleted', ['id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Extra category permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete extra category');
        }
    }

    public function toggleStatus(int $id): ExtraCategory
    {
        try {
            $category = $this->getExtraCategoryById($id);
            $category->update(['status' => !$category->status]);
            LogHelper::statusChanged('extra_categories', $category->id, $category->company_id);

            Log::info('Extra category status toggled', ['id' => $id]);

            return $category->load([ 'miniCategory.subCategory.megaCategory']);
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Extra category status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }

    public function getByMiniCategory(int $miniId): Collection
    {
        return ExtraCategory::byMiniCategory($miniId)
            ->active()
            ->get();
    }
}
