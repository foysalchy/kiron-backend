<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Bin;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\ProductStockLedger;
use App\Models\StockMovementItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{Auth, DB, Log};

class BinService
{
    /**
     * Get all bins with filters
     */
    public function getAllBins(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Bin::with(['warehouse', 'area', 'rack', 'cell']);

            if (isset($filters['warehouse_id'])) {
                $query->where('warehouse_id', $filters['warehouse_id']);
            }

            if (isset($filters['area_id'])) {
                $query->where('area_id', $filters['area_id']);
            }

            if (isset($filters['rack_id'])) {
                $query->where('rack_id', $filters['rack_id']);
            }

            if (isset($filters['cell_id'])) {
                $query->where('cell_id', $filters['cell_id']);
            }

            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('bin_code', 'like', "%{$filters['search']}%")
                        ->orWhere('name', 'like', "%{$filters['search']}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching bins: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch bins');
        }
    }

    /**
     * Get bin by ID
     */
    public function getBinById(int $id): Bin
    {
        $bin = Bin::with(['warehouse', 'area', 'rack', 'cell'])->find($id);

        if (!$bin) {
            throw ApiException::notFound('Bin');
        }

        return $bin;
    }

    /**
     * Create bin
     */
    public function createBin(array $data): Bin
    {
        DB::beginTransaction();

        try {
            // Check if bin_code already exists
            if (Bin::where('bin_code', $data['bin_code'])->exists()) {
                throw ApiException::badRequest('Bin code already exists');
            }

            $data['status'] = $data['status'] ?? 1;

            $bin = Bin::create($data);
            LogHelper::created('bin', $bin->id, $bin->company_id, $bin->name);
            DB::commit();

            Log::info('Bin created successfully', [
                'bin_id' => $bin->id,
                'bin_code' => $bin->bin_code
            ]);


            return $this->getBinById($bin->id);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bin creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create bin: ' . $e->getMessage());
        }
    }

    /**
     * Update bin
     */
    public function updateBin(int $id, array $data): Bin
    {
        DB::beginTransaction();

        try {
            $bin = $this->getBinById($id);

            // Check if bin_code already exists (except current bin)
            if (
                isset($data['bin_code']) &&
                Bin::where('bin_code', $data['bin_code'])
                ->where('id', '!=', $id)
                ->exists()
            ) {
                throw ApiException::badRequest('Bin code already exists');
            }

            $bin->update($data);

            LogHelper::updated('bin', $id, $bin->company_id, $bin->name);
            DB::commit();

            Log::info('Bin updated', ['bin_id' => $id]);

            return $this->getBinById($bin->id);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bin update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update bin');
        }
    }

    /**
     * Delete bin
     */
    public function deleteBin(int $id): void
    {
        DB::beginTransaction();

        try {
            $bin = $this->getBinById($id);

            // Check if bin is used in stock movements
            if ($this->isBinUsedInMovements($id)) {
                throw ApiException::badRequest('Cannot delete bin that is used in stock movements');
            }

            // Check if bin has stock
            if ($this->binHasStock($id)) {
                throw ApiException::badRequest('Cannot delete bin that contains stock');
            }


            LogHelper::deleted('bin', $id, $bin->company_id, $bin->name);
            $bin->delete();

            DB::commit();

            Log::info('Bin deleted', ['bin_id' => $id]);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bin deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete bin');
        }
    }

    /**
     * Change bin status
     */
    public function changeBinStatus(int $id, int $status): Bin
    {
        DB::beginTransaction();

        try {
            $bin = $this->getBinById($id);

            if (!in_array($status, [Status::Inactive->value, Status::Active->value])) {
                throw ApiException::badRequest('Invalid status value');
            }

            // If deactivating, check if bin has stock
            if ($status == Status::Inactive->value && $this->binHasStock($id)) {
                throw ApiException::badRequest('Cannot deactivate bin that contains stock');
            }
            //  status as enum
            $getStatus = Status::from($status);
            $bin->update([
                'status' => $getStatus->value
            ]);

            LogHelper::custom('status_changed', 'bin', $id, $bin->company_id, $bin->name . 'new status' . $getStatus->label());
            DB::commit();

            Log::info('Bin status changed', [
                'bin_id' => $id,
                'status' => $getStatus->label()
            ]);

            return $this->getBinById($bin->id);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bin status change failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to change bin status');
        }
    }

    /**
     * Get bins by warehouse
     */
    public function getBinsByWarehouse(int $warehouseId, bool $activeOnly = true): Collection
    {
        try {
            $query = Bin::where('warehouse_id', $warehouseId)
                ->with(['area', 'rack', 'cell']);

            if ($activeOnly) {
                $query->where('status', 1);
            }

            return $query->orderBy('bin_code')->get();
        } catch (\Exception $e) {
            Log::error('Error fetching bins by warehouse: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch bins');
        }
    }

    /**
     * Get bins by area
     */
    public function getBinsByArea(int $areaId, bool $activeOnly = true): Collection
    {
        try {
            $query = Bin::where('area_id', $areaId)
                ->with(['warehouse', 'rack', 'cell']);

            if ($activeOnly) {
                $query->where('status', 1);
            }

            return $query->orderBy('bin_code')->get();
        } catch (\Exception $e) {
            Log::error('Error fetching bins by area: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch bins');
        }
    }

    /**
     * Get bins by rack
     */
    public function getBinsByRack(int $rackId, bool $activeOnly = true): Collection
    {
        try {
            $query = Bin::where('rack_id', $rackId)
                ->with(['warehouse', 'area', 'cell']);

            if ($activeOnly) {
                $query->where('status', 1);
            }

            return $query->orderBy('bin_code')->get();
        } catch (\Exception $e) {
            Log::error('Error fetching bins by rack: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch bins');
        }
    }


    /**
     * Check if bin is used in stock movements
     */
    private function isBinUsedInMovements(int $binId): bool
    {
        return StockMovementItem::where('source_bin_id', $binId)
            ->orWhere('destination_bin_id', $binId)
            ->exists();
    }

    /**
     * Check if bin has stock
     */
    private function binHasStock(int $binId): bool
    {
        $lastLedger = ProductStockLedger::where('bin_id', $binId)
            ->latest()
            ->first();

        return $lastLedger && $lastLedger->quantity_after > 0;
    }
}
