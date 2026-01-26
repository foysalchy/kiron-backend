<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\SubCategory;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class SubCategoryService
{
    public function getAllSubCategories(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = SubCategory::with(['megaCategory']);



            if (isset($filters['mega_category_id'])) {
                $query->where('mega_category_id', $filters['mega_category_id']);
            }

            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
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
            Log::error('Error fetching sub categories: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch sub categories');
        }
    }


    public function getSubCategoryById(int $id): SubCategory
    {
        $category = SubCategory::with(['megaCategory'])->find($id);

        if (!$category) {
            throw ApiException::notFound('Sub Category');
        }

        return $category;
    }

    public function createSubCategory(array $data): SubCategory
    {
        DB::beginTransaction();

        try {
            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::uploadImage(
                    $data['image'],
                    'categories/sub',
                    'public',
                    2048
                );
            }

            $category = SubCategory::create($data);
            LogHelper::created('sub_category', $category->id, $category->company_id, $category->name);

            DB::commit();

            Log::info('Sub category created successfully', ['id' => $category->id]);

            return $category->load(['megaCategory']);
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['image'])) {
                FileUploadHelper::delete($data['image']);
            }

            Log::error('Sub category creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create sub category');
        }
    }

    public function updateSubCategory(int $id, array $data): SubCategory
    {
        DB::beginTransaction();

        try {
            $category = $this->getSubCategoryById($id);

            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::replace(
                    $data['image'],
                    $category->image,
                    'categories/sub'
                );
            }

            $category->update($data);
            LogHelper::updated('sub_category', $category->id, $category->company_id, $category->name);

            DB::commit();

            Log::info('Sub category updated successfully', ['id' => $category->id]);

            return $category->fresh(['megaCategory']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['image'])) {
                FileUploadHelper::delete($data['image']);
            }

            Log::error('Sub category update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update sub category');
        }
    }

    public function deleteSubCategory(int $id): bool
    {
        try {
            $category = $this->getSubCategoryById($id);
            $category->delete();
            LogHelper::deleted('sub_category', $category->id, $category->company_id, $category->name);

            Log::info('Sub category deleted successfully', ['id' => $id]);

            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Sub category deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete sub category');
        }
    }

    public function restoreSubCategory(int $id): SubCategory
    {
        try {
            $category = SubCategory::withTrashed()->find($id);

            if (!$category) {
                throw ApiException::notFound('Sub Category');
            }

            $category->restore();
            LogHelper::restored('sub_category', $category->id, $category->company_id, $category->name);

            Log::info('Sub category restored successfully', ['id' => $id]);

            return $category->load(['megaCategory']);
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Sub category restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore sub category');
        }
    }

    public function forceDeleteSubCategory(int $id): bool
    {
        DB::beginTransaction();

        try {
            $category = SubCategory::withTrashed()->find($id);

            if (!$category) {
                throw ApiException::notFound('Sub Category');
            }

            FileUploadHelper::delete($category->image);

            $category->forceDelete();

            DB::commit();
            LogHelper::forceDeleted('mega_category', $category->id, $category->company_id, $category->name);

            Log::info('Sub category permanently deleted', ['id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Sub category permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete sub category');
        }
    }

    public function toggleStatus(int $id): SubCategory
    {
        try {
            $category = $this->getSubCategoryById($id);
            $currentStatus = Status::from($category->status);
            // toggle logic
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;
            // update using enum value
            $category->update([
                'status' => $newStatus->value
            ]);
            LogHelper::statusChanged('sub_category', $category->id, $category->company_id, $category->name . ' new status ' . $newStatus->label());
            Log::info('Sub category status toggled', ['id' => $id]);

            return $category->load(['megaCategory']);
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Sub category status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }

    public function getByMegaCategory(int $megaId): Collection
    {
        return SubCategory::byMegaCategory($megaId)
            ->active()
            ->orderBy('order')
            ->get();
    }

    public function searchSubCategories(string $term): Collection
    {
        $query = SubCategory::with(['megaCategory'])
            ->where('name', 'like', "%{$term}%");



        return $query->get();
    }
}
