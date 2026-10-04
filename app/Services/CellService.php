<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Cell;
use App\Models\Rack;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CellService
{
    /**
     * Get all cell with optional pagination
     */
    public function getAllCells(array $filters, bool $paginate = true)
    {
        try {
            $query = Cell::with(['rack', 'warehouse', 'area']);
            if (!empty($filters['select'])) {
                $selectArray = is_string($filters['select']) ? explode(',', $filters['select']) : $filters['select'];
                $query->select($selectArray);
            }

            if (!empty($filters['with'])) {
                $query->with($filters['with']);
            }


            if (!empty($filters['warehouse_id'])) {
                $query->where('warehouse_id', $filters['warehouse_id']);
            }

            if (!empty($filters['area_id'])) {
                $query->where('area_id', $filters['area_id']);
            }

            if (!empty($filters['rack_id'])) {
                $query->where('rack_id', $filters['rack_id']);
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
            Log::error('Error fetching cell: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch cells');
        }
    }

    /**
     * Get cell by ID
     */
    public function getCellById(int $id): Cell
    {
        $cell = Cell::with(['rack', 'warehouse', 'area'])->find($id);
        if (!$cell) {
            throw ApiException::notFound('cell');
        }
        return $cell;
    }

    /**
     * Create a new cell
     */
    public function createCell(array $data): Cell
    {
        DB::beginTransaction();
        try {
            $cell = Cell::create($data);
            LogHelper::created('cell', $cell->id, $cell->company_id, $cell->name);
            DB::commit();
            Log::info('Cell created successfully', ['cell_id' => $cell->id]);

            return $cell->load(['rack']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cell creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create cell');
        }
    }


    /**
     * Update cell
     */
    public function updateCell(int $id, array $data): Cell
    {
        DB::beginTransaction();
        try {
            $cell = $this->getCellById($id);

            $cell->update($data);

            LogHelper::updated('cell', $cell->id, $cell->company_id, $cell->name);
            DB::commit();
            Log::info('Cell Updated Successfully', ['cell_id' => $cell->id]);

            return $cell->fresh(['rack']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cell update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update cell');
        }
    }
    /**
     * Delete cell (soft delete)
     */
    public function deleteCell(int $id): bool
    {
        DB::beginTransaction();
        try {
            $cell = $this->getCellById($id);

            $cell->delete();

            LogHelper::deleted('cell', $cell->id, $cell->company_id, $cell->name);
            DB::commit();
            Log::info('Cell deleted successfully', ['cell_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cell deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete cell');
        }
    }
    /**
     * Restore soft deleted cell
     */
    public function restoreCell(int $id): Cell
    {
        try {
            $cell = Cell::withTrashed()->find($id);
            if (!$cell) {
                throw ApiException::notFound('Cell');
            }
            $cell->restore();
            LogHelper::restored('cell', $cell->id, $cell->company_id, $cell->name);
            return $cell->load(relations: ['rack']);
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Cell restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore data');
        }
    }
    /**
     * Permanently delete an cell
     */
    public function forceDeleteCell(int $id): bool
    {
        try {
            $cell = Cell::withTrashed()->find($id);
            if (!$cell) {
                throw ApiException::notFound('Cell');
            }
            $cell->forceDelete();
            LogHelper::forceDeleted('cell', $id, $cell->company_id, $cell->name);
            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Cell permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete cell');
        }
    }
    /**
     * Toggle rack status (Active/Inactive)
     */
    public function toggleStatus(int $id): Cell
    {
        try {
            $cell = $this->getCellById($id);

            // current status as enum
            $currentStatus = Status::from($cell->status);
            // toggle logic
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;
            // update using enum value
            $cell->update([
                'status' => $newStatus->value
            ]);

            LogHelper::statusChanged('cell', $cell->id, $cell->company_id, $cell->name . ' new status ' . $newStatus->label());
            Log::info('Cell status toggled', ['cell_id' => $id, 'new_status' => $newStatus->label()]);

            return $cell->load(['rack']);
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Cell status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle cell status');
        }
    }
}

