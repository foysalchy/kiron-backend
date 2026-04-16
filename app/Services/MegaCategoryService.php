<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\MegaCategory;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class MegaCategoryService
{
    public function getAllMegaCategories(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = MegaCategory::query();

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
            Log::error('Error fetching mega categories: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch mega categories');
        }
    }


    public function getMegaCategoryById(int $id): MegaCategory
    {
        $category = MegaCategory::find($id);

        if (!$category) {
            throw ApiException::notFound('Mega Category');
        }

        return $category;
    }

    public function createMegaCategory(array $data): MegaCategory
    {
        DB::beginTransaction();

        try {
            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::uploadImage(
                    $data['image'],
                    'categories/mega',
                    'public',
                    2048
                );
            }

            $category = MegaCategory::create($data);
            LogHelper::created('mega_category', $category->id, $category->company_id, $category->name);

            DB::commit();

            Log::info('Mega category created successfully', ['id' => $category->id]);

            return $category;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['image'])) {
                FileUploadHelper::delete($data['image']);
            }

            Log::error('Mega category creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create mega category');
        }
    }

    public function updateMegaCategory(int $id, array $data): MegaCategory
    {
        DB::beginTransaction();
        Log::info($data);
        try {
            $category = $this->getMegaCategoryById($id);

            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::replace(
                    $data['image'],
                    $category->image,
                    'categories/mega'
                );
            }

            $category->update($data);
            LogHelper::updated('mega_category', $category->id, $category->company_id, $category->name);

            DB::commit();

            Log::info('Mega category updated successfully', ['id' => $category->id]);

            return $category;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['image'])) {
                FileUploadHelper::delete($data['image']);
            }

            Log::error('Mega category update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update mega category');
        }
    }

    public function deleteMegaCategory(int $id): bool
    {
        try {
            $category = $this->getMegaCategoryById($id);
            $category->delete();
            LogHelper::deleted('mega_category', $category->id, $category->company_id, $category->name);
            Log::info('Mega category deleted successfully', ['id' => $id]);

            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Mega category deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete mega category');
        }
    }

    public function restoreMegaCategory(int $id): MegaCategory
    {
        try {
            $category = MegaCategory::withTrashed()->find($id);

            if (!$category) {
                throw ApiException::notFound('Mega Category');
            }

            $category->restore();
            LogHelper::restored('mega_category', $category->id, $category->company_id, $category->name);

            Log::info('Mega category restored successfully', ['id' => $id]);

            return $category;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Mega category restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore mega category');
        }
    }

    public function forceDeleteMegaCategory(int $id): bool
    {
        DB::beginTransaction();

        try {
            $category = MegaCategory::withTrashed()->find($id);

            if (!$category) {
                throw ApiException::notFound('Mega Category');
            }

            FileUploadHelper::delete($category->image);

            $category->forceDelete();
            LogHelper::forceDeleted('mega_category', $category->id, $category->company_id, $category->name);

            DB::commit();

            Log::info('Mega category permanently deleted', ['id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Mega category permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete mega category');
        }
    }

    public function toggleStatus(int $id): MegaCategory
    {
        try {
            $category = $this->getMegaCategoryById($id);
            $currentStatus = Status::from($category->status);
            // toggle logic
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;
            // update using enum value
            $category->update([
                'status' => $newStatus->value
            ]);
            LogHelper::statusChanged('mega_category', $category->id, $category->company_id, $category->name . ' new status ' . $newStatus->label());

            Log::info('Mega category status toggled', ['id' => $id]);

            return $category;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Mega category status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}
