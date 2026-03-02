<?php
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\AssetDepreciation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class AssetDepreciationService
{
    /**
     * Get all asset depreciations with optional pagination and filters
     */
    public function getAllDepreciations(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = AssetDepreciation::with(['asset']);

            // Filter by Status (including Trashed)
            if (isset($filters['status'])) {
                if ($filters['status'] === 'Trashed' || $filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }


            // Sorting
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching asset depreciations: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch asset depreciations');
        }
    }

    /**
     * Get asset depreciation by ID
     */
    public function getDepreciationById(int $id): AssetDepreciation
    {
        $depreciation = AssetDepreciation::with(['asset'])->find($id);

        if (!$depreciation) {
            throw ApiException::notFound('asset depreciation');
        }
        return $depreciation;
    }

    /**
     * Create a new asset depreciation
     */
    public function createDepreciation(array $data): AssetDepreciation
    {
        DB::beginTransaction();
        try {
            $depreciation = AssetDepreciation::create($data);

            LogHelper::created('asset_depreciation', $depreciation->id, $depreciation->company_id, $depreciation->asset_id);
            DB::commit();

            Log::info('Asset depreciation created successfully', ['depreciation_id' => $depreciation->id]);

            return $depreciation->load(['asset']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset depreciation creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create asset depreciation');
        }
    }

    /**
     * Update asset depreciation
     */
    public function updateDepreciation(int $id, array $data): AssetDepreciation
    {
        DB::beginTransaction();
        try {
            $depreciation = $this->getDepreciationById($id);

            $depreciation->update($data);

            LogHelper::updated('asset_depreciation', $depreciation->id, $depreciation->company_id,$depreciation->asset_id);
            DB::commit();

            Log::info('Asset depreciation updated successfully', ['depreciation_id' => $depreciation->id]);

            return $depreciation->fresh(['asset']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset depreciation update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update asset depreciation');
        }
    }

    /**
     * Delete asset depreciation (soft delete)
     */
    public function deleteDepreciation(int $id): bool
    {
        DB::beginTransaction();
        try {
            $depreciation = $this->getDepreciationById($id);

            $depreciation->delete();

            LogHelper::deleted('asset_depreciation', $depreciation->id, $depreciation->company_id, $depreciation->asset_id);
            DB::commit();

            Log::info('Asset depreciation deleted successfully', ['depreciation_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset depreciation deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete asset depreciation');
        }
    }

    /**
     * Restore soft deleted asset depreciation
     */
    public function restoreDepreciation(int $id): AssetDepreciation
    {
        DB::beginTransaction();
        try {
            $depreciation = AssetDepreciation::withTrashed()->find($id);
            if (!$depreciation) {
                throw ApiException::notFound('Asset depreciation');
            }

            $depreciation->restore();

            LogHelper::restored('asset_depreciation', $depreciation->id, $depreciation->company_id, $depreciation->asset_id);
            DB::commit();
            Log::info('Asset depreciation restored successfully', ['depreciation_id' => $id]);

            return $depreciation->load(['asset']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset depreciation restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore asset depreciation');
        }
    }
    /**
     * Permanently delete an asset depreciation
     */
    public function forceDeleteDepreciation(int $id): bool
    {
        DB::beginTransaction();
        try {
            $depreciation = AssetDepreciation::withTrashed()->find($id);
            if (!$depreciation) {
                throw ApiException::notFound('Asset depreciation');
            }

            $depreciation->forceDelete();

            LogHelper::forceDeleted('asset_depreciation', $id, $depreciation->company_id, 'Asset ID: '.$depreciation->asset_id);
            DB::commit();
            Log::info('Asset depreciation permanently deleted successfully', ['depreciation_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset depreciation permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete asset depreciation');
        }
    }

    /**
     * Toggle asset depreciation status (Active/Inactive)
     */
    public function toggleStatus(int $id): AssetDepreciation
    {
        DB::beginTransaction();
        try {
            $depreciation = $this->getDepreciationById($id);

            $currentStatus = Status::from($depreciation->status);

            // Toggle logic
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;

            $depreciation->update([
                'status' => $newStatus->value
            ]);

            LogHelper::statusChanged('asset_depreciation', $depreciation->id, $depreciation->company_id, 'Asset ID: '.$depreciation->asset_id .' new status '.$newStatus->label());
            DB::commit();

            Log::info('Asset depreciation status toggled', ['depreciation_id' => $id, 'new_status' => $newStatus->label()]);

            return $depreciation->load(['asset']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset depreciation status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle asset depreciation status');
        }
    }
}
