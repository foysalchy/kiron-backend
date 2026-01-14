<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Area;
use App\Models\Warehouse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AreaService
{
    /**
     * Get all area with optional pagination
     */
    public function getAllAreas(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Area::with(['warehouse']);


            if (!empty($filters['warehouse_id'])) {
                $query->where('warehouse_id', $filters['warehouse_id']);
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
            Log::error('Error fetching areas: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch areas');
        }
    }

    /**
     * Get area by ID
     */
    public function getAreaById(int $id): Area
    {
        $area = Area::with(['warehouse'])->find($id);
        if (!$area) {
            throw ApiException::notFound('area');
        }
        return $area;
    }

    /**
     * Create a new area
     */
    public function createArea(array $data): Area
    {
        try {
            $area =Area::create($data);
            LogHelper::created('area', $area->id, $area->company_id);
            Log::info('Area created successfully', ['area_id' => $area->id]);

            return $area->load(['warehouse']);

        } catch (\Exception $e) {
            Log::error('Area creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create area');
        }
    }


    /**
     * Update area
     */
    public function updateArea(int $id, array $data): Area
    {
        try {
            $area = $this->getAreaById($id);

            $area->update($data);

            LogHelper::updated('area', $area->id, $area->company_id);
            Log::info('Area Updated Successfully', ['area_id' => $area->id]);

            return $area->fresh(['warehouse']);

        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Area update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update area');
        }
    }
    /**
     * Delete area (soft delete)
     */
    public function deleteArea(int $id): bool
    {
        try {
            $area = $this->getAreaById($id);

            $area->delete();

            LogHelper::deleted('area', $area->id, $area->company_id);
            Log::info('Area deleted successfully', ['area_id' => $id]);

            return true;

        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Area deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete area');
        }
    }
    /**
     * Restore soft deleted area
     */
    public function restoreArea(int $id): Area
    {
        try {
            $area = Area::withTrashed()->find($id);
            if (!$area) {
                throw ApiException::notFound('Area');
            }
            $area->restore();
            LogHelper::restored('area',$area->id,$area->company_id);
            return $area->load(['warehouse']);
        } catch (ApiException $e) {
            throw $e;
        }catch(\Exception $e)
        {
            Log::error('Area restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore data');
        }
    }
    /**
     * Permanently delete an area
     */
    public function forceDeleteArea(int $id): bool
    {
        try {
            $area = Area::withTrashed()->find($id);
            if (!$area) {
                throw ApiException::notFound('Area');
            }
            $area->forceDelete();
            LogHelper::forceDeleted('area', $id, $area->company_id);
            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Area permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete area');
        }
    }
    /**
     * Toggle area status (Active/Inactive)
     */
    public function toggleStatus(int $id): Area
    {
        try {
            $area = $this->getAreaById($id);

            $newStatus = $area->status == 1 ? 0 : 1;
            $area->update(['status' => $newStatus]);

            LogHelper::statusChanged('area', $area->id, $area->company_id);
            Log::info('Area status toggled', ['area_id' => $id, 'new_status' => $newStatus]);

            return $area->load(['warehouse']);

        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Area status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle area status');
        }
    }

}
