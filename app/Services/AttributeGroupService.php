<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Company;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\AttributeGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttributeGroupService
{

    public function getAllAttributeGroup(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = AttributeGroup::query();


            // Apply filters
            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }
            if (isset($filters['category'])) {
                $query->where('category', $filters['category']);
            }

            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('category', 'like', "%{$filters['search']}%");
                });
            }

            // Sorting
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            // Return paginated or all
            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching attribute group: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch attribute group');
        }
    }


    /**
     * Get Attribute Group by ID
     */
    public function getAttributeGroupById(int $id): AttributeGroup
    {
        $group = AttributeGroup::find($id);

        if (!$group) {
            throw ApiException::notFound('Attribute Group');
        }

        return $group;
    }

    /**
     * Create a new Attribute Group
     */
    public function createAttributeGroup(array $data): AttributeGroup
    {
        try {

            $group = AttributeGroup::create($data);
            LogHelper::created('attribute_group', $group->id, $group->company_id,$group->name);

            Log::info('Attribute group created successfully', ['attribute group id' => $group->id]);
            return $group;
        } catch (\Exception $e) {

            Log::error('Attribute Group creation failed: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            throw ApiException::serverError('Failed to create Attribute Group');
        }
    }

    /**
     * Update Attribute Group
     */
    public function updateAttributeGroup(int $id, array $data): AttributeGroup
    {


        try {
            $group = $this->getAttributeGroupById($id);
            $group->update($data);
            LogHelper::updated('attribute_group', $group->id, $group->company_id,$group->name);
            Log::info('Attribute Group updated successfully', ['atrribute group id' => $group->id]);
            return $group->fresh();
        } catch (\Exception $e) {

            Log::error('Attribute Group update failed: ' . $e->getMessage(), [
                'attribute_group_id' => $id,
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            throw ApiException::serverError('Failed to update Attribute Group');
        }
    }

    /**
     * Delete Attribute Group (soft delete)
     */
    public function deleteAttributeGroup(int $id): bool
    {
        try {
            $group = $this->getAttributeGroupById($id);

            $group->delete();

            Log::info('Attribute Group deleted successfully', ['Attribute Group id' => $id]);
            LogHelper::deleted('attribute_group', $group->id, $group->company_id,$group->name);

            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Attribute Group deletion failed: ' . $e->getMessage(), [
                'attribute_group_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            throw ApiException::serverError('Failed to delete Attribute Group');
        }
    }

    /**
     * Restore soft deleted Attribute Group
     */
    public function restoreAttributeGroup(int $id): AttributeGroup
    {
        try {
            $group = AttributeGroup::withTrashed()->find($id);

            if (!$group) {
                throw ApiException::notFound('Attribute Group');
            }

            $group->restore();
            LogHelper::restored('attribute_group', $group->id, $group->company_id,$group->name);

            Log::info('Attribute Group restored successfully', ['Attribute Group id' => $id]);

            return $group;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Attribute Group restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore Attribute Group');
        }
    }

    /**
     * Permanently delete company
     */
    public function forceDeleteAttributeGroup(int $id): bool
    {


        try {
            $group = AttributeGroup::withTrashed()->find($id);

            if (!$group) {
                throw ApiException::notFound('Company');
            }

            $group->forceDelete();
            LogHelper::forceDeleted('attribute_group', $group->id, $group->company_id,$group->name);
            Log::info('Attribute Group permanently deleted', ['Attribute Group id' => $id]);

            return true;
        } catch (ApiException $e) {

            throw $e;
        } catch (\Exception $e) {


            Log::error('Attribute Group permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete Attribute Group');
        }
    }

    /**
     * Toggle Attribute Group status
     */
    public function toggleStatus(int $id): AttributeGroup
    {
        try {
            $group = $this->getAttributeGroupById($id);
            // current status as enum
            $currentStatus = Status::from($group->status);

            // toggle logic
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;

            // update using enum value
            $group->update([
                'status' => $newStatus->value
            ]);
            LogHelper::statusChanged('attribute_group', $group->id, $group->company_id,$group->name .' new status ' . $newStatus->label());
            Log::info('Attribute Group status toggled', [
                'atrribute_group_id' => $id,
                'new_status' => $group->status
            ]);

            return $group;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Attribute Group status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle Attribute Group status');
        }
    }
}
