<?php 
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Period;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class PeriodService 
{
    /**
     * Get all periods with optional pagination
     */
    public function getAllPeriods(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Period::with(['periodType']);

            if (!empty($filters['period_type_id'])) {
                $query->where('period_type_id', $filters['period_type_id']);
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
                    $q->where('period_name', 'like', "%{$search}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching periods: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch periods');
        }
    }
    /**
     * Get period by ID
     */
    public function getPeriodById(int $id): Period
    {
        $period = Period::with(['periodType'])->find($id);
        if (!$period) {
            throw ApiException::notFound('Period');
        }
        return $period;
    }

    /**
     * Create a new period
     */
    public function createPeriod(array $data): Period
    {
        DB::beginTransaction();
        try {
            $period = Period::create($data);
            LogHelper::created('period', $period->id, $period->company_id, $period->period_name);
            DB::commit();
            
            return $period->load(['periodType']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Period creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create period');
        }
    }

    /**
     * Update period
     */
    public function updatePeriod(int $id, array $data): Period
    {
        DB::beginTransaction();
        try {
            $period = $this->getPeriodById($id);
            $period->update($data);

            LogHelper::updated('period', $period->id, $period->company_id, $period->period_name);
            DB::commit();

            return $period->fresh(['periodType']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Period update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update period');
        }
    }

    /**
     * Delete period (soft delete)
     */
    public function deletePeriod(int $id): bool
    {
        DB::beginTransaction();
        try {
            $period = $this->getPeriodById($id);
            $period->delete();

            LogHelper::deleted('period', $period->id, $period->company_id, $period->period_name);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Period deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete period');
        }
    }

    /**
     * Restore soft deleted period
     */
    public function restorePeriod(int $id): Period
    {
        DB::beginTransaction();
        try {
            $period = Period::withTrashed()->find($id);
            if (!$period) {
                throw ApiException::notFound('Period');
            }
            $period->restore();
            LogHelper::restored('period', $period->id, $period->company_id, $period->period_name);
            DB::commit();
            return $period->load(['periodType']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to restore period');
        }
    }

    /**
     * Permanently delete a period
     */
    public function forceDeletePeriod(int $id): bool
    {
        DB::beginTransaction();
        try {
            $period = Period::withTrashed()->find($id);
            if (!$period) {
                throw ApiException::notFound('Period');
            }
            $period->forceDelete();
            LogHelper::forceDeleted('period', $id, $period->company_id, $period->period_name);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to permanently delete period');
        }
        
    }
    /**
     * Toggle period status (Active/Inactive)
     */
    public function toggleStatus(int $id): Period
    {
        DB::beginTransaction();
        try {
            $period = $this->getPeriodById($id);

            $currentStatus = Status::from($period->status);
            $newStatus = $currentStatus === Status::Active 
                ? Status::Inactive 
                : Status::Active;

            $period->update([
                'status' => $newStatus->value
            ]);

            LogHelper::statusChanged('period', $period->id, $period->company_id, $period->period_name . ' new status ' . $newStatus->label());

            DB::commit();
            Log::info('Period status toggled', ['period_id' => $id, 'new_status' => $newStatus->label()]);

            return $period->load(['periodType']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Period status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle period status');
        }
    }
}