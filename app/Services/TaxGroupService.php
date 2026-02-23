<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\TaxGroup;
use App\Models\TaxRate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaxGroupService
{
    /**
     * Get all tax groups with filters.
     * tax_rate_ids is a JSON array column (cast: array) — NOT a pivot table.
     */
    public function getAllTaxGroups(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = TaxGroup::query();

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
                        ->orWhere('total_rate', 'like', "%{$search}%");
                });
            }

            $sortBy    = $filters['sort_by']    ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            $result = $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();

            // Attach tax rate name/rate details using the JSON IDs
            $collection = $paginate ? $result->getCollection() : $result;
            $this->attachTaxRateDetails($collection);

            return $result;
        } catch (\Exception $e) {
            Log::error('Tax Group list fetch failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch tax groups');
        }
    }

    /**
     * Get single tax group by ID
     */
    public function getTaxGroupById(int $id): TaxGroup
    {
        $taxGroup = TaxGroup::find($id);

        if (!$taxGroup) {
            throw ApiException::notFound('Tax Group');
        }

        $this->attachTaxRateDetails(collect([$taxGroup]));

        return $taxGroup;
    }

    /**
     * Create a new Tax Group
     */
    public function createTaxGroup(array $data): TaxGroup
    {
        DB::beginTransaction();
        try {
            $taxRateIds = $data['tax_rate_ids'];          // array of IDs
            $totalRate  = $this->calculateTotalRate($taxRateIds);

            $taxGroup = TaxGroup::create([
                'name'         => $data['name'],
                'tax_rate_ids' => $taxRateIds,            // saved as JSON via cast
                'total_rate'   => $totalRate,
                'status'       => Status::Active->value,
            ]);

            LogHelper::created('tax_group', $taxGroup->id, $taxGroup->company_id, $taxGroup->name);
            DB::commit();

            $this->attachTaxRateDetails(collect([$taxGroup]));

            return $taxGroup;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tax Group creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create tax group');
        }
    }

    /**
     * Update an existing Tax Group
     */
    public function updateTaxGroup(int $id, array $data): TaxGroup
    {
        DB::beginTransaction();
        try {
            $taxGroup   = TaxGroup::findOrFail($id);
            $taxRateIds = $data['tax_rate_ids'] ?? $taxGroup->tax_rate_ids;
            $totalRate  = $this->calculateTotalRate($taxRateIds);

            $taxGroup->update([
                'name'         => $data['name'],
                'tax_rate_ids' => $taxRateIds,
                'total_rate'   => $totalRate,
            ]);

            LogHelper::updated('tax_group', $taxGroup->id, $taxGroup->company_id ?? null);
            DB::commit();

            $this->attachTaxRateDetails(collect([$taxGroup]));

            return $taxGroup;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tax Group update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update tax group');
        }
    }

    /**
     * Toggle status Active <-> Inactive
     */
    public function toggleStatus(int $id): TaxGroup
    {
        DB::beginTransaction();
        try {
            $taxGroup = TaxGroup::findOrFail($id);
            $currentStatus = Status::from($taxGroup->status);
            // toggle logic
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;
            // update using enum value
            $taxGroup->update([
                'status' => $newStatus->value
            ]);

            LogHelper::statusChanged('tax_group', $taxGroup->id, $taxGroup->company_id, $taxGroup->name . ' new status ' . $newStatus->label());
            DB::commit();

            return $taxGroup;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tax Group status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle tax group status');
        }
    }

    /**
     * Soft delete
     */
    public function deleteTaxGroup(int $id): bool
    {
        DB::beginTransaction();
        try {
            $taxGroup = TaxGroup::findOrFail($id);
            $taxGroup->delete();

            LogHelper::deleted('tax_group', $taxGroup->id, $taxGroup->company_id ?? null, $taxGroup->name);
            DB::commit();

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tax Group deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete tax group');
        }
    }

    /**
     * Restore soft-deleted
     */
    public function restoreTaxGroup(int $id): TaxGroup
    {
        DB::beginTransaction();
        try {
            $taxGroup = TaxGroup::withTrashed()->find($id);

            if (!$taxGroup) {
                throw ApiException::notFound('Tax Group');
            }

            $taxGroup->restore();

            LogHelper::restored('tax_group', $taxGroup->id, $taxGroup->company_id ?? null, $taxGroup->name);
            DB::commit();

            return $taxGroup;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tax Group restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore tax group');
        }
    }

    /**
     * Permanent delete
     */
    public function forceDeleteTaxGroup(int $id): bool
    {
        DB::beginTransaction();
        try {
            $taxGroup = TaxGroup::withTrashed()->find($id);

            if (!$taxGroup) {
                throw ApiException::notFound('Tax Group');
            }

            $taxGroup->forceDelete();

            LogHelper::forceDeleted('tax_group', $id, $taxGroup->company_id ?? null, $taxGroup->name);
            DB::commit();

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tax Group permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete tax group');
        }
    }

    /**
     * Attach tax rate name+rate details to each TaxGroup as `tax_rates_data`.
     * Uses a single query to load all needed rates at once.
     */
    private function attachTaxRateDetails($groups): void
    {
        $allIds = $groups
            ->flatMap(fn($g) => $g->tax_rate_ids ?? [])
            ->unique()
            ->filter()
            ->values()
            ->toArray();

        if (empty($allIds)) {
            $groups->each(fn($g) => $g->tax_rates_data = []);
            return;
        }

        $rates = TaxRate::whereIn('id', $allIds)
            ->select('id', 'name', 'tax_rate')
            ->get()
            ->keyBy('id');

        $groups->each(function ($group) use ($rates) {
            $group->tax_rates_data = collect($group->tax_rate_ids ?? [])
                ->map(fn($id) => $rates->get($id))
                ->filter()
                ->values()
                ->toArray();
        });
    }

    /**
     * Sum tax_rate values for given IDs
     */
    private function calculateTotalRate(array $taxRateIds): float
    {
        if (empty($taxRateIds)) return 0.0;

        return (float) TaxRate::whereIn('id', $taxRateIds)->sum('tax_rate');
    }
}
