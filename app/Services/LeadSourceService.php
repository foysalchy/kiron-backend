<?php
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\LeadSource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class LeadSourceService
{
    /**
     * Get all Lead Sources with filtering and pagination
     */
    public function getAllLeadSources(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = LeadSource::query();

            // Status Filter
            if (isset($filters['status']) && $filters['status'] !== '') {
                $statusInput = $filters['status'];

                if ($statusInput === 'trashed' || (int)$statusInput === Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $statusInput);
                }
            }

            // Search by Name
            if (isset($filters['search'])) {
                $query->where('name', 'like', "%{$filters['search']}%");
            }

            // Sorting
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching lead sources: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch lead sources');
        }
    }
    /**
     * Get Lead Source by ID
     */
    public function getLeadSourceById(int $id): LeadSource
    {
        $leadSource = LeadSource::find($id);

        if (!$leadSource) {
            throw ApiException::notFound('Lead Source');
        }

        return $leadSource;
    }
    /**
     * Create a new Lead Source
     */
    public function createLeadSource(array $data): LeadSource
    {
        DB::beginTransaction();
        try {
            $leadSource = LeadSource::create($data);

            LogHelper::created('lead_source', $leadSource->id, $leadSource->company_id, $leadSource->name);

            DB::commit();
            Log::info('Lead source created successfully', ['id' => $leadSource->id]);

            return $leadSource;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead Source creation failed: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw ApiException::serverError('Failed to create Lead Source');
        }
    }
    /**
     * Update Lead Source
     */
    public function updateLeadSource(int $id, array $data): LeadSource
    {
        DB::beginTransaction();
        try {
            $leadSource = $this->getLeadSourceById($id);
            $leadSource->update($data);

            LogHelper::updated('lead_source', $leadSource->id, $leadSource->company_id, $leadSource->name);

            DB::commit();
            Log::info('Lead Source updated successfully', ['id' => $id]);

            return $leadSource->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead Source update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update Lead Source');
        }
    }
    /**
     * Soft Delete Lead Source
     */
    public function deleteLeadSource(int $id): bool
    {
        DB::beginTransaction();
        try {
            $leadSource = $this->getLeadSourceById($id);
            $leadSource->delete();

            LogHelper::deleted('lead_source', $leadSource->id, $leadSource->company_id, $leadSource->name);

            DB::commit();
            Log::info('Lead Source soft deleted', ['id' => $id]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead Source deletion failed: ' . $e->getMessage(), ['id' => $id]);
            throw ApiException::serverError('Failed to delete Lead Source');
        }
    }

    /**
     * Restore Lead Source
     */
    public function restoreLeadSource(int $id): LeadSource
    {
        DB::beginTransaction();
        try {
            $leadSource = LeadSource::withTrashed()->find($id);

            if (!$leadSource) {
                throw ApiException::notFound('Lead Source');
            }

            $leadSource->restore();
            LogHelper::restored('lead_source', $leadSource->id, $leadSource->company_id, $leadSource->name);

            DB::commit();
            Log::info('Lead Source restored', ['id' => $id]);
            return $leadSource;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead Source restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore Lead Source');
        }
    }

    /**
     * Permanently delete Lead Source
     */
    public function forceDeleteLeadSource(int $id): bool
    {
        DB::beginTransaction();
        try {
            $leadSource = LeadSource::withTrashed()->find($id);

            if (!$leadSource) {
                throw ApiException::notFound('Lead Source');
            }

            $leadSource->forceDelete();

            LogHelper::forceDeleted('lead_source', $leadSource->id, $leadSource->company_id, $leadSource->name);

            DB::commit();
            Log::info('Lead Source permanently deleted', ['id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead Source permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete Lead Source');
        }
    }

    /**
     * Toggle status
     */
    public function toggleStatus(int $id): LeadSource
    {
        DB::beginTransaction();
        try {
            $leadSource = $this->getLeadSourceById($id);
            $currentStatus = Status::from($leadSource->status);
            $newStatus = $currentStatus === Status::Active ? Status::Inactive : Status::Active;

            $leadSource->update(['status' => $newStatus->value]);

            LogHelper::statusChanged('lead_source', $leadSource->id, $leadSource->company_id, $leadSource->name . ' to ' . $newStatus->name);

            DB::commit();
            return $leadSource;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead Source status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}
