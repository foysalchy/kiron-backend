<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\MiniCategory;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

class MiniCategoryService
{
    public function getAllMiniCategories(array $filters = [], bool $paginate = true, array $columns = ['*']): Collection|LengthAwarePaginator
    {
        try {
            $query = MiniCategory::query();

            if (isset($filters['with'])) {
                $query->with($filters['with']);
            } else {
                $query->with('subCategory', 'megaCategory');
            }


            if (isset($filters['sub_category_id'])) {
                $query->where('sub_category_id', $filters['sub_category_id']);
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
                ? $query->paginate($filters['per_page'] ?? 15, $columns)
                : $query->get($columns);
        } catch (\Exception $e) {
            Log::error('Error fetching mini categories: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch mini categories');
        }
    }


    public function getMiniCategoryById(int $id): MiniCategory
    {
        $category = MiniCategory::with(['subCategory.megaCategory'])->find($id);

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
                $customFileName = Str::slug($data['slug'] ?? $data['name'] ?? 'category') . '_' . time();
                $data['image'] = FileUploadHelper::uploadImage(
                    $data['image'],
                    'categories/mini',
                    'r2',
                    2048,
                    $customFileName
                );
            }

            $category = MiniCategory::create($data);
            LogHelper::created('mini_category', $category->id, $category->company_id, $category->name);

            DB::commit();

            Log::info('Mini category created successfully', ['id' => $category->id]);

            return $category->load(['subCategory.megaCategory']);
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
                $customFileName = Str::slug($data['slug'] ?? $data['name'] ?? $category->slug ?? 'category') . '_' . time();
                $data['image'] = FileUploadHelper::replace(
                    $data['image'],
                    $category->image,
                    'categories/mini',
                    'r2',
                    $customFileName
                );
            }

            $category->update($data);
            LogHelper::updated('mini_category', $category->id, $category->company_id, $category->name);

            DB::commit();

            Log::info('Mini category updated successfully', ['id' => $category->id]);

            return $category->fresh(['subCategory.megaCategory']);
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
            LogHelper::deleted('mini_category', $category->id, $category->company_id, $category->name);

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
            LogHelper::restored('mini_category', $category->id, $category->company_id, $category->name);

            Log::info('Mini category restored successfully', ['id' => $id]);

            return $category->load(['subCategory.megaCategory']);
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
            LogHelper::forceDeleted('mini_category', $category->id, $category->company_id, $category->name);

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
            $currentStatus = Status::from($category->status);
            // toggle logic
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;
            // update using enum value
            $category->update([
                'status' => $newStatus->value
            ]);
            LogHelper::statusChanged('mini_category', $category->id, $category->company_id, $category->name . ' new status ' . $newStatus->label());

            Log::info('Mini category status toggled', ['id' => $id]);

            return $category->load(['subCategory.megaCategory']);
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Mini category status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }

    public function getBySubCategory(int $subId,): Collection
    {
        return MiniCategory::bySubCategory($subId)
            ->active()
            ->get();
    }
}
