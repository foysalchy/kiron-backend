<?php
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\TaxRate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class TaxRateService
{
    /**
     * Get all tax rates with optional pagination and filters
     */
    public function getAllTaxRates(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = TaxRate::query();

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
                      ->orWhere('tax_rate', 'like', "%{$search}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();

        } catch (\Throwable $e) {
            Log::error('Error fetching tax rates: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch tax rates');
        }
    }
    /**
     * Get tax by ID
     */
    public function getTaxRateById(int $id): TaxRate
    {
        $taxRate = TaxRate::find($id);
        if (!$taxRate) {
            throw ApiException::notFound('taxRate');
        }
        return $taxRate;
    }
    /**
     * Create a new tax rate
     */
    public function createTaxRate(array $data): TaxRate
    {
        DB::beginTransaction();
        try {
            $taxRate = TaxRate::create($data);

            LogHelper::created('tax_rate', $taxRate->id, $taxRate->company_id, $taxRate->name);
            Log::info('Tax Rate created successfully', ['tax_rate_id' => $taxRate->id]);

            DB::commit();
            return $taxRate;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tax Rate creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create tax rate');
        }
    }

    /**
     * Update tax rate
     */
    public function updateTaxRate(int $id, array $data): TaxRate
    {
        DB::beginTransaction();
        try {
            $taxRate = $this->getTaxRateById($id);
            $taxRate->update($data);

            LogHelper::updated('tax_rate', $taxRate->id, $taxRate->company_id, $taxRate->name);
            Log::info('Tax Rate updated successfully', ['tax_rate_id' => $taxRate->id]);

            DB::commit();
            return $taxRate->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tax Rate update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update tax rate');
        }
    }

    /**
     * Soft delete tax rate
     */
    public function deleteTaxRate(int $id): bool
    {
        DB::beginTransaction();
        try {
            $taxRate = $this->getTaxRateById($id);
            $taxRate->delete();

            LogHelper::deleted('tax_rate', $taxRate->id, $taxRate->company_id, $taxRate->name);
            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tax Rate deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete tax rate');
        }
    }

    /**
     * Restore soft deleted tax rate
     */
    public function restoreTaxRate(int $id): TaxRate
    {
        DB::beginTransaction();
        try {
            $taxRate = TaxRate::withTrashed()->find($id);
            if (!$taxRate) {
                throw ApiException::notFound('Tax Rate');
            }
            $taxRate->restore();

            LogHelper::restored('tax_rate', $taxRate->id, $taxRate->company_id, $taxRate->id);
            DB::commit();
            return $taxRate;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tax Rate restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore tax rate');
        }
    }
    /**
     * Permanently delete a tax rate
     */
    public function forceDeleteTaxRate(int $id): bool
    {
        DB::beginTransaction();
        try {
            $taxRate = TaxRate::withTrashed()->find($id);

            if (!$taxRate) {
                throw ApiException::notFound('Tax Rate');
            }

            $taxRate->forceDelete();

            LogHelper::forceDeleted('tax_rate', $id, $taxRate->company_id, $taxRate->id);
            Log::info('Tax Rate permanently deleted', ['tax_rate_id' => $id]);

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tax Rate permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete tax rate');
        }
    }

    /**
     * Toggle status (Active/Inactive)
     */
    public function toggleStatus(int $id): TaxRate
    {
        DB::beginTransaction();
        try {
            $taxRate = $this->getTaxRateById($id);
            $newStatus = $taxRate->status == 1 ? 0 : 1;
            $taxRate->update(['status' => $newStatus]);

            LogHelper::statusChanged('tax_rate', $taxRate->id, $taxRate->company_id);
            DB::commit();
            return $taxRate;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tax Rate status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}
