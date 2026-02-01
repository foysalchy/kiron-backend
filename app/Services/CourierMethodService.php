<?php 

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\CourierMethod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class CourierMethodService
{
    /**
     * Get all courier methods with optional pagination
     */
    public function getAllCourierMethods(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = CourierMethod::query();

            // Status Filter (including Trashed)
            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            // Search Filter
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching courier methods: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch courier methods');
        }
    }
    /**
     * Get courier method by ID
     */
    public function getMethodById(int $id): CourierMethod
    {
        $method = CourierMethod::find($id);
        if (!$method) {
            throw ApiException::notFound('Courier Method');
        }
        return $method;
    }
    /**
     * Create a new courier method
     */
    public function createMethod(array $data): CourierMethod
    {
        DB::beginTransaction();
        try {
            $method = CourierMethod::create($data);
            
            LogHelper::created('courier_method', $method->id, $method->company_id, $method->name);
            
            DB::commit();
            Log::info('Courier Method created successfully', ['method_id' => $method->id]);
            return $method;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Courier Method creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create courier method');
        }
    }

    /**
     * Update courier method
     */
    public function updateMethod(int $id, array $data): CourierMethod
    {
        DB::beginTransaction();
        try {
            $method = $this->getMethodById($id);
            $method->update($data);

            LogHelper::updated('courier_method', $method->id, $method->company_id, $method->name);
            
            DB::commit();
            Log::info('Courier Method Updated Successfully', ['method_id' => $method->id]);
            return $method->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Courier Method update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update courier method');
        }
    }

    /**
     * Delete courier method (Soft Delete)
     */
    public function deleteMethod(int $id): bool
    {
        DB::beginTransaction();
        try {
            $method = $this->getMethodById($id);
            $method->delete();

            LogHelper::deleted('courier_method', $method->id, $method->company_id, $method->name);
            
            DB::commit();
            Log::info('Courier Method Deleted Successfully', ['method_id' => $id]);
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Courier Method deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete courier method');
        }
    }

    /**
     * Restore courier method
     */
    public function restoreMethod(int $id): CourierMethod
    {
        DB::beginTransaction();
        try {
            $method = CourierMethod::withTrashed()->find($id);
            if (!$method) {
                throw ApiException::notFound('Courier Method');
            }
            
            $method->restore();
            
            LogHelper::restored('courier_method', $method->id, $method->company_id, $method->name);
            
            DB::commit();
            Log::info('Courier Method Restored Successfully', ['method_id' => $id]);
            return $method;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Courier Method restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore data');
        }
    }

    /**
     * Permanently delete courier method
     */
    public function forceDeleteMethod(int $id): bool
    {
        DB::beginTransaction();
        try {
            $method = CourierMethod::withTrashed()->find($id);

            if (!$method) {
                throw ApiException::notFound('Courier Method');
            }

            $method->forceDelete();
            
            LogHelper::forceDeleted('courier_method', $method->id, $method->company_id, $method->name);

            DB::commit();
            Log::info('Courier Method permanently deleted', ['method_id' => $id]);
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Courier Method permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete courier method');
        }
    }

    /**
     * Toggle courier method status
     */
    public function toggleStatus(int $id): CourierMethod
    {
        DB::beginTransaction();
        try {
            $method = $this->getMethodById($id);
            $currentStatus = Status::from($method->status);
            
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;

            $method->update([
                'status' => $newStatus->value
            ]);

            LogHelper::statusChanged('courier_method', $method->id, $method->company_id, $method->name . ' new status '.$newStatus->label());
            
            DB::commit();
            Log::info('Courier Method status toggled', ['method_id' => $id, 'new_status' => $newStatus->value]);
            return $method;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Courier Method status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle courier method status');
        }
    }
}