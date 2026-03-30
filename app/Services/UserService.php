<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\User;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    /**
     * Get all users with optional pagination
     */

    protected $companyId;

    public function __construct()
    {
        $this->companyId = auth()->user()->company_id;
    }

    public function getAllUsers(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = User::query();

            $query->where('company_id', $this->companyId);

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['role'])) {
                $query->where('role', $filters['role']);
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching users: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch users');
        }
    }

    /**
     * Get user by ID
     */
    public function getUserById(int $id): User
    {
        $user = User::find($id);
        if (!$user) {
            throw ApiException::notFound('User');
        }
        return $user;
    }

    /**
     * Create a new user
     */
    public function createUser(array $data): User
    {
        DB::beginTransaction();
        try {
            // Hash the password before saving
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }
            if (isset($data['profile'])) {
                $data['profile'] = FileUploadHelper::uploadImage(
                    $data['profile'],
                    'users/profile',
                    'public',
                    2048
                );
            }
            $data['status'] = Status::Active->value;
            $data['company_id'] = $this->companyId;
            $user = User::create($data);


            DB::commit();
            Log::info('User created successfully', ['user_id' => $user->id]);

            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create user');
        }
    }

    /**
     * Update user
     */
    public function updateUser(int $id, array $data): User
    {
        DB::beginTransaction();
        try {
            $user = $this->getUserById($id);

            // Handle password update
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }
            if (isset($data['profile'])) {
                $data['profile'] = FileUploadHelper::uploadImage(
                    $data['profile'],
                    'users/profile',
                    'public',
                    2048
                );
            }

            $user->update($data);

            Log::info('User updated successfully', ['user_id' => $user->id]);

            DB::commit();
            return $user->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update user');
        }
    }

    /**
     * Delete user
     */
    public function deleteUser(int $id): bool
    {
        DB::beginTransaction();
        try {
            $user = $this->getUserById($id);

            $user->delete();

            Log::info('User deleted successfully', ['user_id' => $id]);

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete user');
        }
    }

    /**
     * Toggle user status (Active/Inactive)
     */
    public function toggleStatus(int $id): User
    {
        DB::beginTransaction();
        try {
            $user = $this->getUserById($id);

            $newStatus = $user->status == 1 ? 0 : 1;
            $user->update(['status' => $newStatus]);

            Log::info('User status toggled', ['user_id' => $id, 'new_status' => $newStatus]);

            DB::commit();
            return $user;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle user status');
        }
    }
}
