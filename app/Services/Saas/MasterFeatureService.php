<?php

namespace App\Services\Saas;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\MasterFeature;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MasterFeatureService
{
    /**
     * Get all features with optional filters
     */
    public function getAllFeatures(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = MasterFeature::query();

            if (!empty($filters['placement'])) {
                $query->where('placement', $filters['placement']);
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
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('subtitle', 'like', "%{$search}%");
                });
            }

            $query->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_order'] ?? 'desc');

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching master features: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch features');
        }
    }

    /**
     * Get feature by ID
     */
    public function getFeatureById(int $id): MasterFeature
    {
        $feature = MasterFeature::find($id);
        if (!$feature) {
            throw ApiException::notFound('Feature');
        }
        return $feature;
    }

    /**
     * Create a new master feature
     */
    public function createFeature(array $data): MasterFeature
    {
        DB::beginTransaction();
        try {
            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::uploadImage($data['image'], 'saas/features');
            }

            $feature = MasterFeature::create($data);

            LogHelper::created('master_feature', $feature->id, null, $feature->title);

            DB::commit();
            Log::info('Master Feature created successfully', ['feature_id' => $feature->id]);

            return $feature;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Master Feature creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create feature');
        }
    }

    /**
     * Update feature
     */
    public function updateFeature(int $id, array $data): MasterFeature
    {
        DB::beginTransaction();
        try {
            $feature = $this->getFeatureById($id);

            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::replace($data['image'], $feature->image, 'saas/features');
            }

            $feature->update($data);

            LogHelper::updated('master_feature', $feature->id, null, $feature->title);
            DB::commit();
            Log::info('Master Feature Updated Successfully', ['feature_id' => $feature->id]);

            return $feature->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Master Feature update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update feature');
        }
    }

    /**
     * Delete feature (soft delete)
     */
    public function deleteFeature(int $id): bool
    {
        DB::beginTransaction();
        try {
            $feature = $this->getFeatureById($id);
            $feature->delete();

            LogHelper::deleted('master_feature', $feature->id, null, $feature->title);
            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Master Feature deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete feature');
        }
    }

    /**
     * Restore soft deleted feature
     */
    public function restoreFeature(int $id): MasterFeature
    {
        DB::beginTransaction();
        try {
            $feature = MasterFeature::onlyTrashed()->find($id);
            if (!$feature) {
                throw ApiException::notFound('Feature');
            }
            $feature->restore();

            LogHelper::restored('master_feature', $feature->id, null, $feature->title);
            DB::commit();
            return $feature;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Master Feature restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore data');
        }
    }

    /**
     * Permanently delete a feature
     */
    public function forceDeleteFeature(int $id): bool
    {
        DB::beginTransaction();
        try {
            $feature = MasterFeature::withTrashed()->find($id);
            if (!$feature) {
                throw ApiException::notFound('Feature');
            }

            if ($feature->image) {
                FileUploadHelper::delete($feature->image);
            }

            $feature->forceDelete();
            LogHelper::forceDeleted('master_feature', $id, null, $feature->title);
            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Master Feature permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete feature');
        }
    }

    /**
     * Toggle status
     */
    public function toggleStatus(int $id): MasterFeature
    {
        DB::beginTransaction();
        try {
            $feature = $this->getFeatureById($id);
            $newStatus = ($feature->status == Status::Active->value) ? Status::Inactive->value : Status::Active->value;

            $feature->update(['status' => $newStatus]);

            LogHelper::statusChanged('master_feature', $feature->id, null, $feature->title . ' to ' . $newStatus);
            DB::commit();
            return $feature;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}
