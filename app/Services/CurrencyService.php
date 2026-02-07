<?php 
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Currency;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class CurrencyService
{
    /**
     * Get all currencies with optional pagination and filters
     */
    public function getAllCurrencies(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Currency::query();

            // Filter by Status 
            if (isset($filters['status']) && $filters['status'] !== '') {
                if ($filters['status'] === 'trashed' || (int)$filters['status'] === Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('symbol', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching currencies: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch currencies');
        }
    }
    /**
     * Get currency by ID
     */
    public function getCurrencyById(int $id): Currency
    {
        $currency = Currency::find($id);
        if (!$currency) {
            throw ApiException::notFound('Currency');
        }
        return $currency;
    }
    /**
     * Create a new currency
     */
    public function createCurrency(array $data): Currency
    {
        DB::beginTransaction();
        try {
            $currency = Currency::create($data);
            
            LogHelper::created('currency', $currency->id, $currency->company_id, $currency->name);
            
            DB::commit();
            Log::info('Currency created successfully', ['currency_id' => $currency->id]);
            return $currency;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Currency creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create currency');
        }
    }

    /**
     * Update currency
     */
    public function updateCurrency(int $id, array $data): Currency
    {
        DB::beginTransaction();
        try {
            $currency = $this->getCurrencyById($id);
            $currency->update($data);

            LogHelper::updated('currency', $currency->id, $currency->company_id, $currency->name);
            
            DB::commit();
            Log::info('Currency Updated Successfully', ['currency_id' => $currency->id]);
            return $currency->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Currency update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update currency');
        }
    }

    /**
     * Delete currency (Soft Delete)
     */
    public function deleteCurrency(int $id): bool
    {
        DB::beginTransaction();
        try {
            $currency = $this->getCurrencyById($id);
            $currency->delete();

            LogHelper::deleted('currency', $currency->id, $currency->company_id, $currency->name);
            
            DB::commit();
            Log::info('Currency Deleted Successfully', ['currency_id' => $id]);
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Currency deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete currency');
        }
    }

    /**
     * Restore currency
     */
    public function restoreCurrency(int $id): Currency
    {
        DB::beginTransaction();
        try {
            $currency = Currency::withTrashed()->find($id);
            if (!$currency) {
                throw ApiException::notFound('Currency');
            }
            
            $currency->restore();
            
            LogHelper::restored('currency', $currency->id, $currency->company_id, $currency->name);
            
            DB::commit();
            Log::info('Currency Restored Successfully', ['currency_id' => $id]);
            return $currency;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Currency restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore data');
        }
    }

    /**
     * Permanently delete currency
     */
    public function forceDeleteCurrency(int $id): bool
    {
        DB::beginTransaction();
        try {
            $currency = Currency::withTrashed()->find($id);

            if (!$currency) {
                throw ApiException::notFound('Currency');
            }

            $currency->forceDelete();
            
            LogHelper::forceDeleted('currency', $currency->id, $currency->company_id, $currency->name);

            DB::commit();
            Log::info('Currency permanently deleted', ['currency_id' => $id]);
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Currency permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete currency');
        }
    }

    /**
     * Toggle currency status
     */
    public function toggleStatus(int $id): Currency
    {
        DB::beginTransaction();
        try {
            $currency = $this->getCurrencyById($id);
            $currentStatus = Status::from($currency->status);
            
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;

            $currency->update([
                'status' => $newStatus->value
            ]);

            LogHelper::statusChanged('currency', $currency->id, $currency->company_id, $currency->name . ' new status '.$newStatus->label());
            
            DB::commit();
            Log::info('Currency status toggled', ['currency_id' => $id, 'new_status' => $newStatus->value]);
            return $currency;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Currency status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle currency status');
        }
    }
}