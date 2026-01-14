<?php

namespace App\Services;

use App\Models\SubCategory;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class SubCategoryService
{
    public function getAllSubCategories(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = SubCategory::with(['megaCategory']);

            if (isset($filters['company_id'])) {
                $query->where('company_id', $filters['company_id']);
            }

            if (isset($filters['mega_category_id'])) {
                $query->where('mega_category_id', $filters['mega_category_id']);
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
            Log::error('Error fetching sub categories: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch sub categories');
        }
    }
    public function getSubByCompany(array $filters = [], int $companyId, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = SubCategory::with([ 'megaCategory'])->where('company_id',$companyId);

    
            if (isset($filters['mega_category_id'])) {
                $query->where('mega_category_id', $filters['mega_category_id']);
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
            Log::error('Error fetching sub categories: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch sub categories');
        }
    }

    public function getSubCategoryById(int $id): SubCategory
    {
        $category = SubCategory::with(['company', 'megaCategory'])->find($id);

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

            DB::commit();

            Log::info('Sub category created successfully', ['id' => $category->id]);

            return $category->load(['company', 'megaCategory']);
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

            DB::commit();

            Log::info('Sub category updated successfully', ['id' => $category->id]);

            return $category->fresh(['company', 'megaCategory']);
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

            Log::info('Sub category restored successfully', ['id' => $id]);

            return $category->load(['company', 'megaCategory']);
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
            $category->update(['status' => !$category->status]);

            Log::info('Sub category status toggled', ['id' => $id]);

            return $category->load(['company', 'megaCategory']);
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Sub category status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }

    public function getByMegaCategory(int $megaId, int $companyId): Collection
    {
        return SubCategory::byCompany($companyId)
            ->byMegaCategory($megaId)
            ->active()
            ->orderBy('order')
            ->get();
    }

    public function searchSubCategories(string $term, ?int $companyId = null): Collection
    {
        $query = SubCategory::with(['company', 'megaCategory'])
            ->where('name', 'like', "%{$term}%");

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->get();
    }
}
