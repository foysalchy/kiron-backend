<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Employee;
use App\Models\PayRoll;
use App\Models\Position;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Database\Eloquent\Collection;

class PositionService
{
    /**
     * Get all positions with optional filters and pagination
     */
    public function getAllPositions(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Position::with(['payRoll', 'supervisor']);

            if (isset($filters['pay_roll_id'])) {
                $query->where('pay_roll_id', $filters['pay_roll_id']);
            }

            if (isset($filters['type'])) {
                $query->where('type', $filters['type']);
            }
            // Filter by Status
            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching positions: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch positions');
        }
    }

    /**
     * Get position by ID
     */
    public function getPositionById(int $id): Position
    {
        $query = Position::with('payRoll', 'supervisor')->find($id);
        if (!$query) {
            throw ApiException::notFound('Position not found');
        }
        return $query;
    }
    /**
     * create new position
     */
    public function createPosition(array $data): Position
    {
        DB::beginTransaction();
        try {

            $record = Position::create($data);
            LogHelper::created('Position', $record->id, $record->company_id, $record->name . ' Head Count ' . $record->head_count);
            DB::commit();
            return $record->load('payRoll', 'supervisor');
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Position creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create position');
        }
    }
    /**
     * update position
     */
    public function updatePosition(int $id, array $data): Position
    {
        DB::beginTransaction();
        try {
            $record = $this->getPositionById($id);

            $record->update($data);
            LogHelper::updated('Position', $record->id, $record->company_id, $record->name . ' Head Count ' . $record->head_count);
            DB::commit();
            return $record;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Position update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update position');
        }
    }
    /**
     * Soft Delete
     */
    public function deletePosition(int $id): bool
    {
        DB::beginTransaction();
        try {
            $record = $this->getPositionById($id);
            $record->delete();
            LogHelper::deleted('Position', $record->id, $record->company_id, $record->name . ' Head Count ' . $record->head_count);
            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Position deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete position');
        }
    }
    /**
     * restore Soft Delete
     */
    public function restorePosition(int $id): Position
    {
        DB::beginTransaction();
        try {
            $record = Position::withTrashed()->find($id);
            if (!$record || !$record->trashed()) {
                throw ApiException::notFound('Position not found or not deleted');
            }
            $record->restore();
            LogHelper::restored('Position', $record->id, $record->company_id, $record->name . ' Head Count ' . $record->head_count);
            DB::commit();
            return $record->load('payRoll', 'supervisor');
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Position restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore position');
        }
    }
    /**
     * permanently Delete
     */
    public function forceDeletePosition(int $id): bool
    {
        DB::beginTransaction();
        try {
            $record = Position::withTrashed()->find($id);
            if (!$record) {
                throw ApiException::notFound('Position not found');
            }
            $record->forceDelete();
            LogHelper::forceDeleted('Position', $record->id, $record->company_id, $record->name . ' Head Count ' . $record->head_count);
            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Position permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete position');
        }
    }
    /**
     * Toggle Status
     */
    public function toggleStatus(int $id): Position
    {
        DB::beginTransaction();
        try {
            $record = $this->getPositionById($id);
            $currentStatus = Status::from($record->status);
            // toggle logic
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;
            // update using enum value
            $record->update([
                'status' => $newStatus->value
            ]);
            LogHelper::updated('Position Status Toggled', $record->id, $record->company_id, $record->name . ' new status ' . $newStatus->label());
            DB::commit();
            return $record->load('payRoll', 'supervisor');
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Position status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle position status');
        }
    }
}
