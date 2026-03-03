<?php
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\AssetDisposal;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class AssetDisposalService
{
    /**
     * Get all asset disposals with filters
     */
    public function getAllDisposals(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = AssetDisposal::with(['disposalType']);

            // Filter by Status (including Trashed)
            if (isset($filters['status'])) {
                if ($filters['status'] === 'Trashed' || $filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            // Search by note or date
            if (!empty($filters['search'])) {
                $query->where('note', 'like', "%{$filters['search']}%")
                      ->orWhere('date', 'like', "%{$filters['search']}%");
            }

            $query->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_order'] ?? 'desc');

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching asset disposals: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch asset disposals');
        }
    }

    /**
     * Get disposal by ID
     */
    public function getDisposalById(int $id): AssetDisposal
    {
        $disposal = AssetDisposal::with(['disposalType'])->find($id);
        if (!$disposal) {
            throw ApiException::notFound('asset disposal');
        }
        return $disposal;
    }

     /**
     * Create asset disposal
     */
    public function createDisposal(array $data): AssetDisposal
    {
        DB::beginTransaction();
        try {
            $disposal = AssetDisposal::create($data);

            LogHelper::created('asset_disposal', $disposal->id, $disposal->company_id, 'Amount: ' . $disposal->amount);

            DB::commit();

            Log::info('Asset disposal created successfully', ['disposal_id' => $disposal->id]);

            return $disposal->load(['disposalType']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset disposal creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create asset disposal');
        }
    }

    /**
     * Update asset disposal
     */
    public function updateDisposal(int $id, array $data): AssetDisposal
    {
        DB::beginTransaction();
        try {
            $disposal = $this->getDisposalById($id);
            $disposal->update($data);

            LogHelper::updated('asset_disposal', $disposal->id, $disposal->company_id, 'Updated Amount: ' . $disposal->amount);
            DB::commit();

            Log::info('Asset disposal updated successfully', ['disposal_id' => $disposal->id]);
            return $disposal->fresh(['disposalType']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset disposal update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update asset disposal');
        }
    }

    /**
     * Delete (Soft Delete)
     */
    public function deleteDisposal(int $id): bool
    {
        DB::beginTransaction();
        try {
            $disposal = $this->getDisposalById($id);
            $disposal->delete();

            LogHelper::deleted('asset_disposal', $id, $disposal->company_id, 'Amount: ' . $disposal->amount);
            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        }catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to delete asset disposal');
        }
    }

    /**
     * Restore
     */
    public function restoreDisposal(int $id): AssetDisposal
    {
        DB::beginTransaction();
        try {
            $disposal = AssetDisposal::withTrashed()->find($id);
            if (!$disposal) throw ApiException::notFound('Asset disposal');

            $disposal->restore();
            LogHelper::restored('asset_disposal', $id, $disposal->company_id, 'Amount: ' . $disposal->amount);
            DB::commit();

            return $disposal;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        }catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to restore');
        }
    }
    /**
     * Permanently delete an asset disposal
     */
    public function forceDeleteDisposal(int $id): bool
    {
        DB::beginTransaction();
        try {
            $disposal = AssetDisposal::withTrashed()->find($id);
            if (!$disposal) {
                throw ApiException::notFound('Asset disposal');
            }

            $disposal->forceDelete();

            LogHelper::forceDeleted('asset_disposal', $id, $disposal->company_id, 'Amount: ' . $disposal->amount);
            DB::commit();
            Log::info('Asset disposal permanently deleted successfully', ['disposal_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset disposal permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete asset disposal');
        }
    }
    /**
     * Update asset disposal status (Active / Inactive / Disposed)
     */
    public function updateStatus(int $id, int $status): AssetDisposal
    {
        DB::beginTransaction();
        try {
            $disposal = $this->getDisposalById($id);

            $disposal->update([
                'status' => $status
            ]);

            LogHelper::statusChanged('asset_disposal', $disposal->id, $disposal->company_id, 'Status updated to: ' . $status);
            DB::commit();

            Log::info('Asset disposal status updated', ['disposal_id' => $id, 'new_status' => $status]);

            return $disposal->load(['disposalType']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset disposal status update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update asset disposal status');
        }
    }

}
