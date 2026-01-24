<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Holiday;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HolidayService
{
    /**
     * Get all Holidays with filtering and pagination
     */
    public function getAllHolidays(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Holiday::query();

            // Status Filter (Including Trashed)
            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            // Search by Name
            if (isset($filters['search'])) {
                $query->where('name', 'like', "%{$filters['search']}%");
            }

            // Sorting - Default by from_date to see upcoming holidays first
            $sortBy = $filters['sort_by'] ?? 'from_date';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching holidays: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch holidays');
        }
    }

    /**
     * Get Holiday by ID (including trashed if needed)
     */
    public function getHolidayById(int $id, bool $withTrashed = false): Holiday
    {
        $query = Holiday::query();
        if ($withTrashed) $query->withTrashed();

        $holiday = $query->find($id);

        if (!$holiday) {
            throw ApiException::notFound('Holiday');
        }

        return $holiday;
    }

    /**
     * Create a new Holiday with DB Transaction
     */
    public function createHoliday(array $data): Holiday
    {
        DB::beginTransaction();
        try {
            // Calculate number of days based on UI Date Range
            $fromDate = Carbon::parse($data['from_date']);
            $toDate = Carbon::parse($data['to_date']);
            $data['number_of_days'] = $fromDate->diffInDays($toDate) + 1;

            $holiday = Holiday::create($data);

            LogHelper::created('holiday', $holiday->id, $holiday->company_id, $holiday->name);
            Log::info('Holiday created successfully', ['id' => $holiday->id]);

            DB::commit();
            return $holiday;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Holiday creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create Holiday');
        }
    }

    /**
     * Update Holiday with DB Transaction
     */
    public function updateHoliday(int $id, array $data): Holiday
    {
        DB::beginTransaction();
        try {
            $holiday = $this->getHolidayById($id);

            // Re-calculate days if dates are changed
            if (isset($data['from_date']) || isset($data['to_date'])) {
                $fromDate = Carbon::parse($data['from_date'] ?? $holiday->from_date);
                $toDate = Carbon::parse($data['to_date'] ?? $holiday->to_date);
                $data['number_of_days'] = $fromDate->diffInDays($toDate) + 1;
            }

            $holiday->update($data);

            LogHelper::updated('holiday', $holiday->id, $holiday->company_id, $holiday->name);
            Log::info('Holiday updated successfully', ['id' => $id]);

            DB::commit();
            return $holiday->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Holiday update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update Holiday');
        }
    }

    /**
     * Soft Delete Holiday
     */
    public function deleteHoliday(int $id): bool
    {
        DB::beginTransaction();
        try {
            $holiday = $this->getHolidayById($id);
            $holiday->delete();

            LogHelper::deleted('holiday', $holiday->id, $holiday->company_id, $holiday->name);
            Log::info('Holiday soft deleted', ['id' => $id]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Holiday deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete Holiday');
        }
    }

    /**
     * Restore Soft Deleted Holiday
     */
    public function restoreHoliday(int $id): Holiday
    {
        DB::beginTransaction();
        try {
            $holiday = Holiday::withTrashed()->find($id);

            if (!$holiday) {
                throw ApiException::notFound('Holiday');
            }

            $holiday->restore();
            LogHelper::restored('holiday', $holiday->id, $holiday->company_id, $holiday->name);
            Log::info('Holiday restored successfully', ['id' => $id]);

            DB::commit();
            return $holiday;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Holiday restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore Holiday');
        }
    }

    /**
     * Permanently Delete Holiday (Force Delete)
     */
    public function forceDeleteHoliday(int $id): bool
    {
        DB::beginTransaction();
        try {
            $holiday = Holiday::withTrashed()->find($id);

            if (!$holiday) {
                throw ApiException::notFound('Holiday');
            }

            $holiday->forceDelete();

            LogHelper::forceDeleted('holiday', $holiday->id, $holiday->company_id, $holiday->name);
            Log::info('Holiday permanently deleted', ['id' => $id]);

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Holiday force delete failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete Holiday');
        }
    }

    /**
     * Toggle Holiday status
     */
    public function toggleStatus(int $id): Holiday
    {
        DB::beginTransaction();
        try {
            $holiday = $this->getHolidayById($id);
            $currentStatus = Status::from($holiday->status);
            $newStatus = $currentStatus === Status::Active ? Status::Inactive : Status::Active;

            $holiday->update(['status' => $newStatus->value]);

            LogHelper::statusChanged('holiday', $holiday->id, $holiday->company_id, $holiday->name . ' to ' . $newStatus->label());

            DB::commit();
            return $holiday;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Holiday status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}
