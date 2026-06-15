<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Models\PricingPackage;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Database\Eloquent\Collection;

class PricingPackageService
{
    /**
     * Get all pricing packages with optional filters and pagination
     */
    public function getAllPricingPackages(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = PricingPackage::with('tiers');

            // Filter by Status
            if (isset($filters['status'])) {
                if ($filters['status'] === 'Trashed' || $filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            // Search by Name
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            $sortBy    = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();

        } catch (\Exception $e) {
            Log::error('Error fetching pricing packages: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch pricing packages');
        }
    }

    /**
     * Get pricing package by ID
     */
    public function getPricingPackageById(int $id): PricingPackage
    {
        $package = PricingPackage::with('tiers')->find($id);

        if (!$package) {
            throw ApiException::notFound('Pricing package not found');
        }

        return $package;
    }

    /**
     * Create a new pricing package with tiers
     */
    public function createPricingPackage(array $data): PricingPackage
    {
        DB::beginTransaction();
        try {
            $tiers = $data['tiers'] ?? [];
            unset($data['tiers']);

            $package = PricingPackage::create($data);

            if (!empty($tiers)) {
                $package->tiers()->createMany($tiers);
            }

            DB::commit();
            Log::info('Pricing package created successfully', ['package_id' => $package->id]);
            return $package->load('tiers');

        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pricing package creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create pricing package');
        }
    }

    /**
     * Update an existing pricing package with tiers
     */
    public function updatePricingPackage(int $id, array $data): PricingPackage
    {

    \Log::info($data);
        DB::beginTransaction();
        try {
            $package = $this->getPricingPackageById($id);

            $tiers = $data['tiers'] ?? [];
            unset($data['tiers']);

            $package->update($data);

            // Sync tiers — remove billing cycles not in the new payload
            $providedCycles = collect($tiers)->pluck('billing_cycle')->toArray();
            $package->tiers()->whereNotIn('billing_cycle', $providedCycles)->delete();

            foreach ($tiers as $tier) {
                $package->tiers()->updateOrCreate(
                    ['billing_cycle' => $tier['billing_cycle']],
                    [
                        'regular_price'  => $tier['regular_price'],
                        'discount_price' => $tier['discount_price'] ?? null,
                    ]
                );
            }

            DB::commit();
            Log::info('Pricing package updated successfully', ['package_id' => $package->id]);
            return $package->fresh('tiers');

        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pricing package update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update pricing package');
        }
    }

    /**
     * Soft delete a pricing package
     */
    public function deletePricingPackage(int $id): bool
    {
        DB::beginTransaction();
        try {
            $package = $this->getPricingPackageById($id);
            $package->delete();

            DB::commit();
            Log::info('Pricing package soft deleted successfully', ['package_id' => $package->id]);
            return true;

        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pricing package deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete pricing package');
        }
    }

    /**
     * Restore a soft-deleted pricing package
     */
    public function restorePricingPackage(int $id): PricingPackage
    {
        DB::beginTransaction();
        try {
            $package = PricingPackage::withTrashed()->find($id);

            if (!$package || !$package->trashed()) {
                throw ApiException::notFound('Pricing package not found or not deleted');
            }

            $package->restore();

            DB::commit();
            Log::info('Pricing package restored successfully', ['package_id' => $package->id]);
            return $package->load('tiers');

        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pricing package restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore pricing package');
        }
    }

    /**
     * Permanently delete a pricing package and its tiers
     */
    public function forceDeletePricingPackage(int $id): bool
    {
        DB::beginTransaction();
        try {
            $package = PricingPackage::withTrashed()->find($id);

            if (!$package) {
                throw ApiException::notFound('Pricing package not found');
            }

            // Remove all tiers before force deleting the package
            $package->tiers()->delete();
            $package->forceDelete();

            DB::commit();
            Log::info('Pricing package permanently deleted', ['package_id' => $id]);
            return true;

        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pricing package permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete pricing package');
        }
    }

    /**
     * Toggle status between Active and Inactive
     */
    public function toggleStatus(int $id): PricingPackage
    {
        DB::beginTransaction();
        try {
            $package = $this->getPricingPackageById($id);

            $currentStatus = Status::from($package->status);
            $newStatus     = ($currentStatus === Status::Active) ? Status::Inactive : Status::Active;

            $package->update(['status' => $newStatus->value]);

            DB::commit();
            Log::info('Pricing package status toggled', ['package_id' => $id, 'new_status' => $newStatus]);
            return $package->fresh('tiers');

        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pricing package status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle pricing package status');
        }
    }
}