<?php
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\LeadStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class LeadStatusService
{
    /**
     * Get all Lead Statuses with filtering and pagination
     */
    public function getAllLeadStatuses(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = LeadStatus::query();

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
            $sortOrder = $filters['sort_order'] ?? 'asc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching lead statuses: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch lead statuses');
        }
    }
    /**
     * Get Lead Status by ID
     */
    public function getLeadStatusById(int $id): LeadStatus
    {
        $leadStatus = LeadStatus::find($id);

        if (!$leadStatus) {
            throw ApiException::notFound('Lead Status');
        }

        return $leadStatus;
    }

    /**
     * Create a new Lead Status
     */
    public function createLeadStatus(array $data): LeadStatus
    {
        DB::beginTransaction();
        try {
            $leadStatus = LeadStatus::create($data);

            LogHelper::created('lead_status', $leadStatus->id, $leadStatus->company_id, $leadStatus->name);
            DB::commit();
            Log::info('Lead status created successfully', ['id' => $leadStatus->id]);

            return $leadStatus;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead Status creation failed: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw ApiException::serverError('Failed to create Lead Status');
        }
    }
    /**
     * Update Lead Status with DB Transaction
     */
    public function updateLeadStatus(int $id, array $data): LeadStatus
    {
        DB::beginTransaction();
        try {
            $leadStatus = $this->getLeadStatusById($id);
            $leadStatus->update($data);

            LogHelper::updated('lead_status', $leadStatus->id, $leadStatus->company_id, $leadStatus->name);
            Log::info('Lead Status updated successfully', ['id' => $id]);

            DB::commit();
            return $leadStatus->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead Status update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update Lead Status');
        }
    }

    /**
     * Soft Delete Lead Status
     */
    public function deleteLeadStatus(int $id): bool
    {
        DB::beginTransaction();
        try {
            $leadStatus = $this->getLeadStatusById($id);
            $leadStatus->delete();

            LogHelper::deleted('lead_status', $leadStatus->id, $leadStatus->company_id, $leadStatus->name);

            DB::commit();
            Log::info('Lead Status soft deleted', ['id' => $id]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead Status deletion failed: ' . $e->getMessage(), ['id' => $id]);
            throw ApiException::serverError('Failed to delete Lead Status');
        }
    }

    /**
     * Restore Lead Status
     */
    public function restoreLeadStatus(int $id): LeadStatus
    {
        DB::beginTransaction();
        try {
            $leadStatus = LeadStatus::withTrashed()->find($id);

            if (!$leadStatus) {
                throw ApiException::notFound('Lead Status');
            }

            $leadStatus->restore();
            LogHelper::restored('lead_status', $leadStatus->id, $leadStatus->company_id, $leadStatus->name);
            DB::commit();
            Log::info('Lead Status restored', ['id' => $id]);
            return $leadStatus;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead Status restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore Lead Status');
        }
    }

    /**
     * Permanently delete Lead Status (Force Delete)
     */
    public function forceDeleteLeadStatus(int $id): bool
    {
        DB::beginTransaction();
        try {
            $leadStatus = LeadStatus::withTrashed()->find($id);

            if (!$leadStatus) {
                throw ApiException::notFound('Lead Status');
            }

            $leadStatus->forceDelete();

            LogHelper::forceDeleted('lead_status', $leadStatus->id, $leadStatus->company_id, $leadStatus->name);
            Log::info('Lead Status permanently deleted', ['id' => $id]);

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead Status permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete Lead Status');
        }
    }

    /**
     * Toggle status with DB Transaction
     */
    public function toggleStatus(int $id): LeadStatus
    {
        DB::beginTransaction();
        try {
            $leadStatus = $this->getLeadStatusById($id);
            $currentStatus = Status::from($leadStatus->status);
            $newStatus = $currentStatus === Status::Active ? Status::Inactive : Status::Active;

            $leadStatus->update(['status' => $newStatus->value]);

            LogHelper::statusChanged('lead_status', $leadStatus->id, $leadStatus->company_id, $leadStatus->name . ' to ' . $newStatus->name);

            DB::commit();
            return $leadStatus;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead Status status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}
