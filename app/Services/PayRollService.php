<?php

namespace App\Services;

use App\Models\PayRoll;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class PayRollService
{
    /**
     * Get all payrolls with optional pagination
     */
    public function getAllPayRolls(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = PayRoll::query();

            // Search by name
            if (!empty($filters['search'])) {
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
            Log::error('Error fetching payrolls: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch payroll records');
        }
    }

    /**
     * Get payroll by ID
     */
    public function getPayRollById(int $id): PayRoll
    {
        $payRoll = PayRoll::find($id);

        if (!$payRoll) {
            throw ApiException::notFound('PayRoll');
        }

        return $payRoll;
    }

    /**
     * Create a new payroll
     */
    public function createPayRoll(array $data): PayRoll
    {
        DB::beginTransaction();

        try {
            $payRoll = PayRoll::create($data);

            // Log the creation
            LogHelper::created('payroll', $payRoll->id, $payRoll->company_id);

            DB::commit();
            Log::info('PayRoll created successfully', ['id' => $payRoll->id]);

            return $payRoll;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PayRoll creation failed: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw ApiException::serverError('Failed to create payroll');
        }
    }

    /**
     * Update payroll
     */
    public function updatePayRoll(int $id, array $data): PayRoll
    {
        DB::beginTransaction();

        try {
            $payRoll = $this->getPayRollById($id);
            $payRoll->update($data);

            // Log the update
            LogHelper::updated('payroll', $payRoll->id, $payRoll->company_id);

            DB::commit();
            Log::info('PayRoll updated successfully', ['id' => $id]);

            return $payRoll->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PayRoll update failed: ' . $e->getMessage(), ['id' => $id]);
            throw ApiException::serverError('Failed to update payroll');
        }
    }

    /**
     * Delete payroll (Soft Delete)
     */
    public function deletePayRoll(int $id): bool
    {
        DB::beginTransaction();

        try {
            $payRoll = $this->getPayRollById($id);
            $payRoll->delete();

            // Log the deletion
            LogHelper::deleted('payroll', $payRoll->id, $payRoll->company_id);

            DB::commit();
            Log::info('PayRoll soft deleted', ['id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PayRoll deletion failed: ' . $e->getMessage(), ['id' => $id]);
            throw ApiException::serverError('Failed to delete payroll');
        }
    }

    /**
     * Restore soft deleted payroll
     */
    public function restorePayRoll(int $id): PayRoll
    {
        DB::beginTransaction();

        try {
            $payRoll = PayRoll::withTrashed()->find($id);

            if (!$payRoll) {
                throw ApiException::notFound('PayRoll');
            }

            $payRoll->restore();
            LogHelper::restored('payroll', $payRoll->id, $payRoll->company_id);

            DB::commit();
            return $payRoll;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PayRoll restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore payroll');
        }
    }

    /**
     * Permanently delete payroll
     */
    public function forceDeletePayRoll(int $id): bool
    {
        DB::beginTransaction();

        try {
            $payRoll = PayRoll::withTrashed()->find($id);

            if (!$payRoll) {
                throw ApiException::notFound('PayRoll');
            }

            $payRoll->forceDelete();
            LogHelper::forceDeleted('payroll', $payRoll->id, $payRoll->company_id);

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PayRoll permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete payroll');
        }
    }
}

