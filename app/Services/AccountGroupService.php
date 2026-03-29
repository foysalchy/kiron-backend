<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\AccountGroup;
use App\Models\AccountType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class AccountGroupService
{

    /**
     * Get all account groups with optional pagination
     */
    public function getAllGroups(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = AccountGroup::query();

            // Filter by Status
            if (isset($filters['status'])) {
                if ($filters['status'] === 'Trashed' || $filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            // Search by Name
            if (isset($filters['search']) && $filters['search'] !== '') {
                $query->where('name', 'like', "%{$filters['search']}%");
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching account groups: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch account groups');
        }
    }

    /**
     * Get group by ID
     */
    public function accountType()
    {
        return AccountType::all();
    }
    /**
     * Get group by ID
     */
    public function getGroupById(int $id): AccountGroup
    {
        $group = AccountGroup::find($id);
        if (!$group) {
            throw ApiException::notFound('Account Group');
        }
        return $group;
    }

    /**
     * Create a new account group
     */
    public function createGroup(array $data): AccountGroup
    {
        DB::beginTransaction();
        try {
            $group = AccountGroup::create($data);

            LogHelper::created('account_group', $group->id, $group->company_id, $group->name);
            DB::commit();

            Log::info('Account Group created successfully', ['group_id' => $group->id]);
            return $group->load(['accountType']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Account Group creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create account group');
        }
    }

    /**
     * Update account group
     */
    public function updateGroup(int $id, array $data): AccountGroup
    {
        DB::beginTransaction();
        try {
            $group = $this->getGroupById($id);
            $group->update($data);

            LogHelper::updated('account_group', $group->id, $group->company_id, $group->name);
            DB::commit();

            return $group->fresh(['accountType']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Account Group update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update account group');
        }
    }

    /**
     * Delete account group
     */
    public function deleteGroup(int $id): bool
    {
        DB::beginTransaction();
        try {
            $group = $this->getGroupById($id);
            $group->delete();

            LogHelper::deleted('account_group', $id, $group->company_id, $group->name);
            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Account Group deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete account group');
        }
    }
    /**
     * Restore soft deleted account group
     */
    public function restoreGroup(int $id): AccountGroup
    {
        DB::beginTransaction();
        try {
            $group = AccountGroup::withTrashed()->find($id);

            if (!$group) {
                throw ApiException::notFound('Account Group');
            }

            $group->restore();
            LogHelper::restored('account_group', $group->id, $group->company_id, $group->name);

            DB::commit();
            Log::info('Account Group restored successfully', ['group_id' => $id]);

            return $group->load(['accountType']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Account Group restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore account group');
        }
    }

    /**
     * Permanently delete an account group
     */
    public function forceDeleteGroup(int $id): bool
    {
        DB::beginTransaction();
        try {
            $group = AccountGroup::withTrashed()->find($id);

            if (!$group) {
                throw ApiException::notFound('Account Group');
            }

            $group->forceDelete();
            LogHelper::forceDeleted('account_group', $id, $group->company_id, $group->name);

            DB::commit();
            Log::info('Account Group permanently deleted', ['group_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Account Group permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete account group');
        }
    }

    // Toggle Status Method
    public function toggleStatus(int $id): AccountGroup
    {
        DB::beginTransaction();
        try {
            $group = $this->getGroupById($id);
            $newStatus = $group->status === Status::Active->value ? Status::Inactive : Status::Active;

            $group->update(['status' => $newStatus->value]);

            LogHelper::statusChanged('account_group', $group->id, $group->company_id, $group->name . ' new status ' . $newStatus->label());
            DB::commit();
            return $group;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}
