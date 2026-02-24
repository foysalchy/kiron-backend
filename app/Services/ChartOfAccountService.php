<?php
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class ChartOfAccountService
{ 
    /**
     * Get all accounts with optional pagination and filters
     */
    public function getAllAccounts(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = ChartOfAccount::with(['accountGroup.accountType']);

            // Filter by Status
            if (isset($filters['status'])) {
                if ($filters['status'] === 'Trashed' || $filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            // Filter by Group
            if (!empty($filters['account_group_id'])) {
                $query->where('account_group_id', $filters['account_group_id']);
            }

            // Search by Name
            if (isset($filters['search']) && $filters['search'] !== '') {
                $query->where('name', 'like', "%{$filters['search']}%");
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching chart of accounts: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch accounts');
        }
    }
    /**
     * Get account by ID
     */
    public function getAccountById(int $id): ChartOfAccount
    {
        $account = ChartOfAccount::with(['accountGroup.accountType'])->find($id);
        if (!$account) {
            throw ApiException::notFound('Account');
        }
        return $account;
    }
    /**
     * Create a new chart of account
     */
    public function createAccount(array $data): ChartOfAccount
    {
        DB::beginTransaction();
        try {
            $account = ChartOfAccount::create($data);

            LogHelper::created('chart_of_account', $account->id, $account->company_id, $account->name);
            DB::commit();

            Log::info('Chart of Account created successfully', ['account_id' => $account->id]);
            return $account->load(['accountGroup.accountType']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Chart of Account creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create account');
        }
    }

    /**
     * Update chart of account
     */
    public function updateAccount(int $id, array $data): ChartOfAccount
    {
        DB::beginTransaction();
        try {
            $account = $this->getAccountById($id);
            $account->update($data);

            LogHelper::updated('chart_of_account', $account->id, $account->company_id, $account->name);
            DB::commit();

            return $account->fresh(['accountGroup.accountType']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Chart of Account update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update account');
        }
    }
    /**
     * Delete chart of account (Soft Delete)
     */
    public function deleteAccount(int $id): bool
    {
        DB::beginTransaction();
        try {
            $account = $this->getAccountById($id);
            $account->delete();

            LogHelper::deleted('chart_of_account', $id, $account->company_id, $account->name);
            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Chart of Account deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete account');
        }
    }

    /**
     * Restore soft deleted chart of account
     */
    public function restoreAccount(int $id): ChartOfAccount
    {
        DB::beginTransaction();
        try {
            $account = ChartOfAccount::withTrashed()->find($id);

            if (!$account) {
                throw ApiException::notFound('Account');
            }

            $account->restore();
            LogHelper::restored('chart_of_account', $account->id, $account->company_id, $account->name);

            DB::commit();
            Log::info('Chart of Account restored successfully', ['account_id' => $id]);

            return $account->load(['accountGroup.accountType']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Chart of Account restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore account');
        }
    }

    /**
     * Permanently delete a chart of account
     */
    public function forceDeleteAccount(int $id): bool
    {
        DB::beginTransaction();
        try {
            $account = ChartOfAccount::withTrashed()->find($id);

            if (!$account) {
                throw ApiException::notFound('Account');
            }

            $account->forceDelete();
            LogHelper::forceDeleted('chart_of_account', $id, $account->company_id, $account->name);

            DB::commit();
            Log::info('Chart of Account permanently deleted', ['account_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Chart of Account permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete account');
        }
    }

    /**
     * Toggle Account Status
     */
    public function toggleStatus(int $id): ChartOfAccount
    {
        DB::beginTransaction();
        try {
            $account = $this->getAccountById($id);
            $newStatus = $account->status === Status::Active->value ? Status::Inactive : Status::Active;

            $account->update(['status' => $newStatus->value]);

            LogHelper::statusChanged('chart_of_account', $account->id, $account->company_id, $account->name . ' new status ' . $newStatus->label());
            DB::commit();
            return $account;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}
