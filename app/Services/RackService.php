<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Cell;
use App\Models\Rack;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RackService
{
    /**
     * Get all rack with optional pagination
     */
    public function getAllRacks(array $filters, bool $paginate = true)
    {
        try {
            $query = Rack::with(['warehouse', 'area']);


            if (!empty($filters['warehouse_id'])) {
                $query->where('warehouse_id', $filters['warehouse_id']);
            }
            if (!empty($filters['area_id'])) {
                $query->where('area_id', $filters['area_id']);
            }


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
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching rack: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch racks');
        }
    }

    /**
     * Get rack by ID
     */
    public function getRackById(int $id): Rack
    {
        $rack = Rack::with(['warehouse', 'area'])->find($id);
        if (!$rack) {
            throw ApiException::notFound('rack');
        }
        return $rack;
    }

    /**
     * Create a new rack
     */
    public function createRack(array $data): Rack
    {
        DB::beginTransaction();
        try {
            $rack = Rack::create($data);
            LogHelper::created('rack', $rack->id, $rack->company_id);
            Log::info('Rack created successfully', ['rack_id' => $rack->id]);

            DB::commit();
            return $rack->load(['area']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Rack creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create rack');
        }
    }


    /**
     * Update rack
     */
    public function updateRack(int $id, array $data): Rack
    {
        DB::beginTransaction();
        try {
            $rack = $this->getRackById($id);

            $rack->update($data);

            LogHelper::updated('rack', $rack->id, $rack->company_id);
            Log::info('Rack Updated Successfully', ['rack_id' => $rack->id]);

            DB::commit();
            return $rack->fresh(['area']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Rack update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update rack');
        }
    }
    /**
     * Delete rack (soft delete)
     */
    public function deleteRack(int $id): bool
    {
        DB::beginTransaction();
        try {
            $rack = $this->getRackById($id);

            $rack->delete();

            LogHelper::deleted('rack', $rack->id, $rack->company_id);
            Log::info('Rack deleted successfully', ['rack_id' => $id]);

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Rack deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete rack');
        }
    }
    /**
     * Restore soft deleted rack
     */
    public function restoreRack(int $id): Rack
    {
        DB::beginTransaction();
        try {
            $rack = Rack::withTrashed()->find($id);
            if (!$rack) {
                throw ApiException::notFound('Rack');
            }
            $rack->restore();
            LogHelper::restored('rack', $rack->id, $rack->company_id);
            DB::commit();
            return $rack->load(relations: ['area']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Rack restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore data');
        }
    }
    /**
     * Permanently delete an rack
     */
    public function forceDeleteRack(int $id): bool
    {
        DB::beginTransaction();
        try {
            $rack = Rack::withTrashed()->find($id);
            if (!$rack) {
                throw ApiException::notFound('Rack');
            }
            $rack->forceDelete();
            LogHelper::forceDeleted('rack', $id, $rack->company_id);
            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Rack permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete rack');
        }
    }
    /**
     * Toggle rack status (Active/Inactive)
     */
    public function toggleStatus(int $id): Rack
    {
        DB::beginTransaction();
        try {
            $rack = $this->getRackById($id);

            $newStatus = $rack->status == 1 ? 0 : 1;
            $rack->update(['status' => $newStatus]);

            LogHelper::statusChanged('rack', $rack->id, $rack->company_id);
            Log::info('Rack status toggled', ['rack_id' => $id, 'new_status' => $newStatus]);

            DB::commit();
            return $rack->load(['area']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Rack status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle rack status');
        }
    }
}
