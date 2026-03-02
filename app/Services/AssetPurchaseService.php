<?php
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\AssetPurchase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class AssetPurchaseService
{
    /**
     * Get all asset purchases with optional pagination and filters
     */
    public function getAllPurchases(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = AssetPurchase::with(['asset', 'supplier']);

            // Filter by Status (including Trashed)
            if (isset($filters['status'])) {
                if ($filters['status'] === 'Trashed' || $filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            // Search by Invoice Number or related fields
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                      ->orWhereHas('asset', function ($sq) use ($search) {
                          $sq->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Filter by Asset
            if (!empty($filters['asset_id'])) {
                $query->where('asset_id', $filters['asset_id']);
            }

            // Filter by Supplier (Party)
            if (!empty($filters['supplier_id'])) {
                $query->where('supplier_id', $filters['supplier_id']);
            }

            // Sorting
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching asset purchases: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch asset purchases');
        }
    }

    /**
     * Get asset purchase by ID
     */
    public function getPurchaseById(int $id): AssetPurchase
    {
        $purchase = AssetPurchase::with(['asset', 'supplier'])->find($id);

        if (!$purchase) {
            throw ApiException::notFound('asset purchase');
        }
        return $purchase;
    }

    /**
     * Create a new asset purchase
     */
    public function createPurchase(array $data): AssetPurchase
    {
        DB::beginTransaction();
        try {
            $purchase = AssetPurchase::create($data);

            LogHelper::created('asset_purchase', $purchase->id, $purchase->company_id, $purchase->invoice_number );
            DB::commit();

            Log::info('Asset purchase created successfully', ['purchase_id' => $purchase->id]);

            return $purchase->load(['asset', 'supplier']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset purchase creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create asset purchase');
        }
    }

    /**
     * Update asset purchase
     */
    public function updatePurchase(int $id, array $data): AssetPurchase
    {
        DB::beginTransaction();
        try {
            $purchase = $this->getPurchaseById($id);

            $purchase->update($data);

            LogHelper::updated('asset_purchase', $purchase->id, $purchase->company_id, $purchase->invoice_number );
            DB::commit();

            Log::info('Asset purchase updated successfully', ['purchase_id' => $purchase->id]);

            return $purchase->fresh(['asset', 'supplier']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset purchase update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update asset purchase');
        }
    }

    /**
     * Delete asset purchase (soft delete)
     */
    public function deletePurchase(int $id): bool
    {
        DB::beginTransaction();
        try {
            $purchase = $this->getPurchaseById($id);

            $purchase->delete();

            LogHelper::deleted('asset_purchase', $purchase->id, $purchase->company_id, $purchase->invoice_number);
            DB::commit();

            Log::info('Asset purchase deleted successfully', ['purchase_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset purchase deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete asset purchase');
        }
    }

    /**
     * Restore soft deleted asset purchase
     */
    public function restorePurchase(int $id): AssetPurchase
    {
        DB::beginTransaction();
        try {
            $purchase = AssetPurchase::withTrashed()->find($id);
            if (!$purchase) {
                throw ApiException::notFound('Asset purchase');
            }

            $purchase->restore();

            LogHelper::restored('asset_purchase', $purchase->id, $purchase->company_id, $purchase->invoice_number);
            DB::commit();
            Log::info('Asset purchase restored successfully', ['purchase_id' => $id]);

            return $purchase->load(['asset', 'supplier']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset purchase restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore asset purchase');
        }
    }

    /**
     * Permanently delete an asset purchase
     */
    public function forceDeletePurchase(int $id): bool
    {
        DB::beginTransaction();
        try {
            $purchase = AssetPurchase::withTrashed()->find($id);
            if (!$purchase) {
                throw ApiException::notFound('Asset purchase');
            }

            $purchase->forceDelete();

            LogHelper::forceDeleted('asset_purchase', $id, $purchase->company_id, $purchase->invoice_number);
            DB::commit();
            Log::info('Asset purchase permanently deleted successfully', ['purchase_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset purchase permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete asset purchase');
        }
    }

    /**
     * Toggle asset purchase status (Active/Inactive)
     */
    public function toggleStatus(int $id): AssetPurchase
    {
        DB::beginTransaction();
        try {
            $purchase = $this->getPurchaseById($id);

            $currentStatus = Status::from($purchase->status);

            // Toggle logic
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;

            $purchase->update([
                'status' => $newStatus->value
            ]);

            LogHelper::statusChanged('asset_purchase', $purchase->id, $purchase->company_id, $purchase->invoice_number .' new status '.$newStatus->label());
            DB::commit();

            Log::info('Asset purchase status toggled', ['purchase_id' => $id, 'new_status' => $newStatus->label()]);

            return $purchase->load(['asset', 'supplier']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset purchase status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle asset purchase status');
        }
    }
}
