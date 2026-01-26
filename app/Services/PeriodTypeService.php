<?php 
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\PeriodType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class PeriodTypeService 
{
    /**
     * Get all period types with optional pagination
     */
    public function getAllPeriodTypes(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = PeriodType::query();

            // Filter by Status
            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            // Search by Type Name
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('type', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching period types: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch period types');
        }
    }
    /**
     * Get period type by ID
     */
    public function getPeriodTypeById(int $id): PeriodType
    {
        $periodType = PeriodType::find($id);
        if (!$periodType) {
            throw ApiException::notFound('Period Type');
        }
        return $periodType;
    }

    /**
     * Create a new period type
     */
    public function createPeriodType(array $data): PeriodType
    {
        DB::beginTransaction();
        try {
            $periodType = PeriodType::create($data);
            
            LogHelper::created('period_type', $periodType->id, $periodType->company_id, $periodType->type);
            
            DB::commit();
            Log::info('Period Type created successfully', ['period_type_id' => $periodType->id]);

            return $periodType;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Period Type creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create period type');
        }
    }

    /**
     * Update period type
     */
    public function updatePeriodType(int $id, array $data): PeriodType
    {
        DB::beginTransaction();
        try {
            $periodType = $this->getPeriodTypeById($id);

            $periodType->update($data);

            LogHelper::updated('period_type', $periodType->id, $periodType->company_id, $periodType->type);
            
            DB::commit();
            Log::info('Period Type Updated Successfully', ['period_type_id' => $periodType->id]);

            return $periodType->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Period Type update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update period type');
        }
    }

    /**
     * Delete period type (soft delete)
     */
    public function deletePeriodType(int $id): bool
    {
        DB::beginTransaction();
        try {
            $periodType = $this->getPeriodTypeById($id);

            $periodType->delete();

            LogHelper::deleted('period_type', $periodType->id, $periodType->company_id, $periodType->type);
            
            DB::commit();
            Log::info('Period Type deleted successfully', ['period_type_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Period Type deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete period type');
        }
    }
    /**
     * Restore soft deleted period type
     */
    public function restorePeriodType(int $id): PeriodType
    {
        DB::beginTransaction();
        try {
            $periodType = PeriodType::withTrashed()->find($id);
            if (!$periodType) {
                throw ApiException::notFound('Period Type');
            }

            $periodType->restore();

            LogHelper::restored('period_type', $periodType->id, $periodType->company_id, $periodType->type);
            
            DB::commit();
            Log::info('Period Type restored successfully', ['period_type_id' => $id]);

            return $periodType;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Period Type restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore period type');
        }
    }

    /**
     * Permanently delete a period type
     */
    public function forceDeletePeriodType(int $id): bool
    {
        DB::beginTransaction();
        try {
            $periodType = PeriodType::withTrashed()->find($id);
            if (!$periodType) {
                throw ApiException::notFound('Period Type');
            }

            $periodType->forceDelete();

            LogHelper::forceDeleted('period_type', $id, $periodType->company_id, $periodType->type);
            
            DB::commit();
            Log::info('Period Type permanently deleted', ['period_type_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Period Type permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete period type');
        }
    }

    /**
     * Toggle status (Active/Inactive)
     */
    public function toggleStatus(int $id): PeriodType
    {
        DB::beginTransaction();
        try {
            $periodType = $this->getPeriodTypeById($id);

            $currentStatus = Status::from($periodType->status);
            $newStatus = $currentStatus === Status::Active ? Status::Inactive : Status::Active;

            $periodType->update(['status' => $newStatus->value]);

            LogHelper::statusChanged('period_type', $periodType->id, $periodType->company_id, $periodType->type . ' new status ' . $newStatus->label());
            
            DB::commit();
            return $periodType;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Period Type status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}