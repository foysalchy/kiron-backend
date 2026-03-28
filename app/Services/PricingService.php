<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Pricing;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Database\Eloquent\Collection;

class PricingService
{
    /**
     * Get all pricing plans with optional filters and pagination
     */
    public function getAllPricings(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Pricing::query();

            // Filter by Status
            if (isset($filters['status'])) {
                if ($filters['status'] === 'Trashed' || $filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            // Search by Name or Sub Title
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sub_title', 'like', "%{$search}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 5)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching pricing plans: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch pricing plans');
        }
    }

    /**
     * Get pricing plan by ID
     */
    public function getPricingById(int $id): Pricing
    {
        $pricing = Pricing::find($id);
        if (!$pricing) {
            throw ApiException::notFound('Pricing plan not found');
        }
        return $pricing;
    }

/**
 * Create a new pricing plan
 */
public function createPricing(array $data): Pricing
{
    DB::beginTransaction();
    try {
        $record = Pricing::create($data);

        // LogHelper::created('Pricing', $record->id, 0, "Plan Name: {$record->name}");

        DB::commit();
        Log::info('Pricing Plan created successfully', ['plan_id' => $record->id]);
        return $record;
    } catch (ApiException $e) {
        DB::rollBack();
        throw $e;
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Pricing creation failed: ' . $e->getMessage());
        throw ApiException::serverError('Failed to create pricing plan');
    }
}

    /**
     * Update an existing pricing plan
     */
    public function updatePricing(int $id, array $data): Pricing
    {
        DB::beginTransaction();
        try {
            $record = $this->getPricingById($id);

            $record->update($data);

            // LogHelper::updated('Pricing', $record->id, $record->company_id ?? null, "Updated Plan: {$record->name}");

            DB::commit();
            Log::info('Pricing Plan updated successfully', ['plan_id' => $record->id]);
            return $record->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pricing update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update pricing plan');
        }
    }

    /**
     * Soft Delete a pricing plan
     */
    public function deletePricing(int $id): bool
    {
        DB::beginTransaction();
        try {
            $record = $this->getPricingById($id);
            $record->delete();

            DB::commit();
            Log::info('Pricing Plan deleted successfully', ['plan_id' => $record->id]);
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pricing deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete pricing plan');
        }
    }

    /**
     * Restore a soft-deleted pricing plan
     */
    public function restorePricing(int $id): Pricing
    {
        DB::beginTransaction();
        try {
            $record = Pricing::withTrashed()->find($id);
            if (!$record || !$record->trashed()) {
                throw ApiException::notFound('Pricing plan not found or not deleted');
            }

            $record->restore();

            DB::commit();
            Log::info('Pricing Plan restored successfully', ['plan_id' => $record->id]);
            return $record;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pricing restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore pricing plan');
        }
    }

    /**
     * Permanently delete a pricing plan
     */
    public function forceDeletePricing(int $id): bool
    {
        DB::beginTransaction();
        try {
            $record = Pricing::withTrashed()->find($id);
            if (!$record) {
                throw ApiException::notFound('Pricing plan not found');
            }

            $record->forceDelete();

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pricing permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete pricing plan');
        }
    }

    /**
     * Toggle status between Active and Inactive
     */
    public function toggleStatus(int $id): Pricing
    {
        DB::beginTransaction();
        try {
            $record = $this->getPricingById($id);

            // Toggle Logic using Enum
            $currentStatus = Status::from($record->status);
            $newStatus = ($currentStatus === Status::Active) ? Status::Inactive : Status::Active;

            $record->update(['status' => $newStatus->value]);

            DB::commit();
            Log::info('Pricing Plan status toggled', ['plan_id' => $id, 'new_status' => $newStatus]);
            return $record;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pricing status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle pricing status');
        }
    }
}
