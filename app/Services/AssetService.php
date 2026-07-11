<?php
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\Asset;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class AssetService
{
    /**
     * Get all assets with optional pagination and filters
     */
    public function getAllAssets(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Asset::with(['category', 'manager', 'company']);

            // Filter by Status (including Trashed)
            if (isset($filters['status'])) {
                if ($filters['status'] === 'Trashed' || $filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('asset_tag', 'like', "%{$search}%")
                      ->orWhere('serial_number', 'like', "%{$search}%");
                });
            }

            if (!empty($filters['asset_category_id'])) {
                $query->where('asset_category_id', $filters['asset_category_id']);
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching assets: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch assets');
        }
    }

    /**
     * Get asset by ID
     */
    public function getAssetById(int $id): Asset
    {
        $asset = Asset::with(['category'])->find($id);

        if (!$asset) {
            throw ApiException::notFound('Asset');
        }

        return $asset;
    }

    /**
     * Create a new asset
     */
public function createAsset(array $data): Asset
    {
        DB::beginTransaction();

        try {
            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::uploadImage(
                    $data['image'],
                    'assets/images',
                   
                );
            }
            
            $asset = Asset::create($data);
            
            LogHelper::created('asset', $asset->id, $asset->company_id, $asset->name);

            DB::commit();
            Log::info('Asset created successfully', ['asset_id' => $asset->id]);

            return $asset->load(['category']);

        } catch (UniqueConstraintViolationException $e) {
            DB::rollBack();
            if (isset($data['image'])) {
                FileUploadHelper::delete($data['image']);
            }
            Log::error('Asset creation failed (Duplicate): ' . $e->getMessage());

            throw ApiException::badRequest('This asset tag is already in use within this company.');

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($data['image'])) {
                FileUploadHelper::delete($data['image']);
            }
            Log::error('Asset creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create asset');
        }
    }

    /**
     * Update asset
     */
    public function updateAsset(int $id, array $data): Asset
    {
        DB::beginTransaction();

        try {
            $asset = $this->getAssetById($id);

            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::replace(
                    $data['image'],
                    $asset->image,
                    'assets/images'
                );
            }

            $asset->update($data);
            LogHelper::updated('asset', $asset->id, $asset->company_id, $asset->name);

            DB::commit();
            Log::info('Asset updated successfully', ['asset_id' => $asset->id]);

            return $asset->fresh(['category']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($data['image'])) {
                FileUploadHelper::delete($data['image']);
            }
            Log::error('Asset update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update asset');
        }
    }

    /**
     * Delete asset (soft delete)
     */
    public function deleteAsset(int $id): bool
    {
        DB::beginTransaction();
        try {
            $asset = $this->getAssetById($id);
            $asset->delete();
            LogHelper::deleted('asset', $asset->id, $asset->company_id, $asset->name);
            DB::commit();
            Log::info('Asset deleted successfully', ['asset_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete asset');
        }
    }

    /**
     * Restore soft deleted asset
     */
    public function restoreAsset(int $id): Asset
    {
        DB::beginTransaction();
        try {
            $asset = Asset::withTrashed()->find($id);

            if (!$asset) {
                throw ApiException::notFound('Asset');
            }

            $asset->restore();
            LogHelper::restored('asset', $asset->id, $asset->company_id, $asset->name);

            DB::commit();
            Log::info('Asset restored successfully', ['asset_id' => $id]);
            return $asset->load(['category']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore asset');
        }
    }

    /**
     * Permanently delete asset
     */
    public function forceDeleteAsset(int $id): bool
    {
        DB::beginTransaction();

        try {
            $asset = Asset::withTrashed()->find($id);

            if (!$asset) {
                throw ApiException::notFound('Asset');
            }

            if ($asset->image) {
                FileUploadHelper::delete($asset->image);
            }

            $asset->forceDelete();
            LogHelper::forceDeleted('asset', $asset->id, $asset->company_id, $asset->name);

            DB::commit();
            Log::info('Asset permanently deleted', ['asset_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete asset');
        }
    }
    /**
     * Update asset status (Active/Inactive/Disposed)
     */
    public function updateStatus(int $id, int $status): Asset
    {
        DB::beginTransaction();
        try {
            $asset = $this->getAssetById($id);

            $statusEnum = Status::from($status);

            $asset->update([
                'status' => $statusEnum->value
            ]);

            LogHelper::statusChanged('asset', $asset->id, $asset->company_id, $asset->name . ' changed to ' . $statusEnum->label());

            DB::commit();
            Log::info('Asset status updated', ['asset_id' => $id, 'new_status' => $statusEnum->label()]);

            return $asset->load(['category', 'manager']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset status update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update asset status');
        }
    }
}
