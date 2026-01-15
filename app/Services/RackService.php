<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Cell;
use App\Models\Rack;
use Illuminate\Support\Facades\Log;

class RackService
{
    /**
     * Get all rack with optional pagination
     */
    public function getAllRacks(array $filters, bool $paginate = true)
    {
        try {
            $query = Rack::with(['area']);

            if (!empty($filters['area_id'])) {
                $query->where('area_id', $filters['area_id']);
            }

            if (isset($filters['status']) && $filters['status'] !== "") {
                $query->where('status', (int)$filters['status']);
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
        $rack = Rack::with(['area'])->find($id);
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
        try {
            $rack =Rack::create($data);
            LogHelper::created('rack', $rack->id, $rack->company_id);
            Log::info('Rack created successfully', ['rack_id' => $rack->id]);

            return $rack->load(['area']);

        } catch (\Exception $e) {
            Log::error('Rack creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create rack');
        }
    }


    /**
     * Update rack
     */
    public function updateRack(int $id, array $data): Rack
    {
        try {
            $rack = $this->getRackById($id);

            $rack->update($data);

            LogHelper::updated('rack', $rack->id, $rack->company_id);
            Log::info('Rack Updated Successfully', ['rack_id' => $rack->id]);

            return $rack->fresh(['area']);

        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Rack update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update rack');
        }
    }
    /**
     * Delete rack (soft delete)
     */
    public function deleteRack(int $id): bool
    {
        try {
            $rack = $this->getRackById($id);

            $rack->delete();

            LogHelper::deleted('rack', $rack->id, $rack->company_id);
            Log::info('Rack deleted successfully', ['rack_id' => $id]);

            return true;

        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Rack deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete rack');
        }
    }
    /**
     * Restore soft deleted rack
     */
    public function restoreRack(int $id): Rack
    {
        try {
            $rack = Rack::withTrashed()->find($id);
            if (!$rack) {
                throw ApiException::notFound('Rack');
            }
            $rack->restore();
            LogHelper::restored('rack',$rack->id,$rack->company_id);
            return $rack->load(relations: ['area']);
        } catch (ApiException $e) {
            throw $e;
        }catch(\Exception $e)
        {
            Log::error('Rack restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore data');
        }
    }
    /**
     * Permanently delete an rack
     */
    public function forceDeleteRack(int $id): bool
    {
        try {
            $rack = Rack::withTrashed()->find($id);
            if (!$rack) {
                throw ApiException::notFound('Rack');
            }
            $rack->forceDelete();
            LogHelper::forceDeleted('rack', $id, $rack->company_id);
            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Rack permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete rack');
        }
    }
    /**
     * Toggle rack status (Active/Inactive)
     */
    public function toggleStatus(int $id): Rack
    {
        try {
            $rack = $this->getRackById($id);

            $newStatus = $rack->status == 1 ? 0 : 1;
            $rack->update(['status' => $newStatus]);

            LogHelper::statusChanged('rack', $rack->id, $rack->company_id);
            Log::info('Rack status toggled', ['rack_id' => $id, 'new_status' => $newStatus]);

            return $rack->load(['area']);

        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Rack status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle rack status');
        }
    }

}
