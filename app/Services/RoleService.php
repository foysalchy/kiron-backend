<?php

namespace App\Services;

use App\Models\Role;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleService
{
    public function getAllRoles(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {

    
        try {
            $query = Role::with(['permissions', 'users']); // ✅ users যোগ হলো

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
            Log::error('Error fetching roles: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch roles');
        }
    }

    public function getRoleById(int $id): Role
    {
        $role = Role::with('permissions')->find($id);
        if (!$role) throw ApiException::notFound('Role');
        return $role;
    }

    public function createRole(array $data): Role
    {
        DB::beginTransaction();
        try {
            // Assign to current company automatically

            $role = Role::create($data);

            // Sync Permissions via Pivot Table
            if (isset($data['permissions'])) {
                $role->permissions()->sync($data['permissions']);
            }

            //  LogHelper::created('role', $role->id, $role->company_id, $role->name);
            DB::commit();
            return $role;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Role creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create role');
        }
    }

    public function updateRole(int $id, array $data): Role
    {
        DB::beginTransaction();
        try {
            $role = $this->getRoleById($id);
            $role->update($data);


            if (isset($data['permissions'])) {
                $role->permissions()->sync($data['permissions']);
            }

            //  LogHelper::updated('role', $role->id, $role->company_id, $role->name);
            DB::commit();
            return $role;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Role update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update role');
        }
    }

    public function deleteRole(int $id): bool
    {
        try {
            $role = $this->getRoleById($id);
            $role->permissions()->detach(); // Clean up pivot table
            $role->delete();
            return true;
        } catch (\Exception $e) {
            throw ApiException::serverError('Failed to delete role');
        }
    }
}
