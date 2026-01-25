<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WarehouseService
{
    /**
     * Get all warehouses with optional pagination
     */
    public function getAllWarehouses(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Warehouse::query();

            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching warehouses: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch warehouses');
        }
    }

    /**
     * Get warehouse by ID
     */
    public function getWarehouseById(int $id): Warehouse
    {
        $warehouse = Warehouse::find($id);
        if (!$warehouse) {
            throw ApiException::notFound('Warehouse');
        }
        return $warehouse;
    }

    /**
     * Create a new warehouse
     */
    public function createWarehouse(array $data): Warehouse
    {
        DB::beginTransaction();
        try {
            $warehouse = Warehouse::create($data);
            LogHelper::created('warehouse', $warehouse->id, $warehouse->company_id);
            DB::commit();
            Log::info('Warehouse created successfully', ['warehouse_id' => $warehouse->id]);

            return $warehouse;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Warehouse creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create warehouse');
        }
    }


    /**
     * Update warehouse
     */
    public function updateWarehouse(int $id, array $data): Warehouse
    {
        DB::beginTransaction();
        try {
            $warehouse = $this->getWarehouseById($id);

            $warehouse->update($data);

            LogHelper::updated('warehouse', $warehouse->id, $warehouse->company_id);
            Log::info('Warehouse Updated Successfully', ['warehouse_id' => $warehouse->id]);

            DB::commit();
            return $warehouse->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Warehouse update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update warehouse');
        }
    }

    /**
     * Delete warehouse (soft delete)
     */
    public function deleteWarehouse(int $id): bool
    {
        DB::beginTransaction();
        try {
            $warehouse = $this->getWarehouseById($id);

            $warehouse->delete();

            LogHelper::deleted('warehouse', $warehouse->id, $warehouse->company_id);
            Log::info('Warehouse deleted successfully', ['warehouse_id' => $id]);

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Warehouse deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete warehouse');
        }
    }

    /**
     * Restore soft deleted warehouse
     */

    public function restoreWarehouse(int $id): Warehouse
    {
        DB::beginTransaction();
        try {
            $warehouse = Warehouse::withTrashed()->find($id);
            if (!$warehouse) {
                throw ApiException::notFound('Warehouse');
            }
            $warehouse->restore();
            LogHelper::restored('warehouse', $warehouse->id, $warehouse->company_id);
            Log::info('Warehouse restored successfully', ['warehouse_id' => $id]);
            DB::commit();
            return $warehouse;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Warehouse restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore warehouse');
        }
    }

    /**
     * Permanently delete a warehouse (Force Delete)
     */
    public function forceDeleteWarehouse(int $id): bool
    {
        DB::beginTransaction();
        try {
            $warehouse = Warehouse::withTrashed()->find($id);

            if (!$warehouse) {
                throw ApiException::notFound('Warehouse');
            }

            $warehouse->forceDelete();

            LogHelper::forceDeleted('warehouse', $id, $warehouse->company_id);
            Log::info('Warehouse permanently deleted', ['warehouse_id' => $id]);

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Warehouse permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete warehouse');
        }
    }

    /**
     * Toggle warehouse status (Active/Inactive)
     */
    public function toggleStatus(int $id): Warehouse
    {
        DB::beginTransaction();
        try {
            $warehouse = $this->getWarehouseById($id);

            $newStatus = $warehouse->status == 1 ? 0 : 1;
            $warehouse->update(['status' => $newStatus]);

            LogHelper::statusChanged('warehouse', $warehouse->id, $warehouse->company_id);
            Log::info('Warehouse status toggled', ['warehouse_id' => $id, 'new_status' => $newStatus]);

            DB::commit();
            return $warehouse;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Warehouse status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle warehouse status');
        }
    }
}
