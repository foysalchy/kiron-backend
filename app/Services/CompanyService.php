<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Company;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\CompanyUpdateRequest;
use App\Models\DomainSetup;
use App\Models\EmailVerification;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompanyService
{

    public function getAllCompanies(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Company::with([
                'primaryUser',
                'pricingPackage',
                'currentSubscription.pricingPackage',
                'currentMonthlyUsage',
                // 'domainSetup' <- eager load remove kora holo
            ])
                ->withCount([
                    'products as product_used' => fn($q) => $q->withoutGlobalScopes(),
                    'users as user_used',

                    'domains as domain_used' => fn($q) => $q->withoutGlobalScopes(),

                ]);

            // Existing filters
            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (isset($filters['business_type'])) {
                $query->where('business_type', $filters['business_type']);
            }

            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name',  'like', "%{$filters['search']}%")
                        ->orWhere('email', 'like', "%{$filters['search']}%")
                        ->orWhere('phone', 'like', "%{$filters['search']}%");
                });
            }

            // ── New filters ──

            if (isset($filters['pricing_package_id'])) {
                $query->whereHas(
                    'currentSubscription',
                    fn($q) =>
                    $q->where('pricing_package_id', $filters['pricing_package_id'])
                );
            }

            if (isset($filters['registered_from'])) {
                $query->whereDate('created_at', '>=', $filters['registered_from']);
            }
            if (isset($filters['registered_to'])) {
                $query->whereDate('created_at', '<=', $filters['registered_to']);
            }

            if (isset($filters['expire_from'])) {
                $query->whereHas(
                    'currentSubscription',
                    fn($q) =>
                    $q->whereDate('ends_at', '>=', $filters['expire_from'])
                );
            }
            if (isset($filters['expire_to'])) {
                $query->whereHas(
                    'currentSubscription',
                    fn($q) =>
                    $q->whereDate('ends_at', '<=', $filters['expire_to'])
                );
            }

            if (isset($filters['free_trial'])) {
                if ($filters['free_trial'] == 1) {
                    $query->whereHas(
                        'currentSubscription',
                        fn($q) =>
                        $q->whereNotNull('trial_ends_at')
                            ->where('trial_ends_at', '>', now())
                    );
                } else {
                    $query->whereDoesntHave(
                        'currentSubscription',
                        fn($q) =>
                        $q->whereNotNull('trial_ends_at')
                            ->where('trial_ends_at', '>', now())
                    );
                }
            }

            if (isset($filters['expiring_in_days'])) {
                $days = (int) $filters['expiring_in_days'];
                $query->whereHas(
                    'currentSubscription',
                    fn($q) =>
                    $q->whereDate('ends_at', '<=', now()->addDays($days))
                        ->whereDate('ends_at', '>=', now())
                );
            }

            $sortBy    = $filters['sort_by']    ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            $result = $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();

            // ── domain_setup alada query diye load + attach ──
            $companyIds = $result->pluck('id');

            $domainSetups = DomainSetup::withoutGlobalScopes()->get()->keyBy('company_id');
            $verifications = EmailVerification::withoutGlobalScope('company')
                ->whereIn('company_id', $companyIds)
                ->whereNotNull('verified_at')
                ->get()
                ->groupBy('company_id');
            $result->each(function ($company) use ($domainSetups, $verifications) {
                $company->setRelation(
                    'domainSetup',
                    $domainSetups->get($company->id)
                );
                $companyVerifications = $verifications->get($company->id, collect());

                $company->setAttribute('verification_status', [
                    'email_verified' => $companyVerifications->contains('method', 'email'),
                    'phone_verified' => $companyVerifications->contains('method', 'sms'),
                ]);
            });

            return $result;

            return $result;
        } catch (\Exception $e) {
            Log::error('Error fetching companies: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch companies');
        }
    }
    /**
     * Get company by ID
     */
    public function getCompanyById(int $id): Company
    {

        $company = Company::with([
            'primaryUser',                          // is_primary = 1
            'currentSubscription.pricingPackage',   // active subscription + package
            'orders' => fn($q) => $q->latest()->limit(10),
            'users.roles',
            'subscriptions' => fn($q) => $q->with(['pricingPackage', 'extraOrderCharges.order']),
            'updateRequests' => fn($q) => $q->latest()->limit(3), // company update requests

            'subscriptions.subscriptionPayments.customerPaymentMethod',

            'domains',
            'loginHistories.user',
            'currentMonthlyUsage',
        ])
            ->withCount([
                'products as product_used' => fn($q) => $q->withoutGlobalScopes(),
                'users as user_used',
                'domains as domain_used' => fn($q) => $q->withoutGlobalScopes(),

            ])

            ->find($id);


        foreach ($company->subscriptions as $sub) {
            $startMonth = \Carbon\Carbon::parse($sub->starts_at)->format('Y-m');
            $endMonth   = \Carbon\Carbon::parse($sub->ends_at)->format('Y-m');

            $charges = \App\Models\ExtraOrderCharge::with('order:id,order_no')
                ->where('company_id', $id)
                ->whereBetween('month', [$startMonth, $endMonth])
                ->get();

            // Debug
            Log::info("Sub ID: {$sub->id} | Start: {$startMonth} | End: {$endMonth} | Charges: " . $charges->count());

            $sub->setRelation('extra_order_charges', $charges);
        }
        $company->setAttribute('verification_status', $company->getVerificationStatus());


        return $company;
    }

    /**
     * Create a new company
     */
    public function createCompany(array $data): Company
    {
        DB::beginTransaction();

        try {
            // Handle logo upload using helper
            if (isset($data['logo'])) {
                $data['logo'] = FileUploadHelper::uploadImage(
                    $data['logo'],
                    'companies/logos',
                    2048 // 2MB max
                );
            }

            $company = Company::create($data);
            LogHelper::created('company', $company->id, $company->id);

            // Seed default Tally Account Groups and Chart of Accounts for new company
            \App\Services\DefaultAccountingSeederService::seedDefaultAccountsForCompany($company->id);

            DB::commit();

            Log::info('Company created successfully', ['company_id' => $company->id]);

            return $company;
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded logo if exists
            if (isset($data['logo'])) {
                FileUploadHelper::delete($data['logo']);
            }

            Log::error('Company creation failed: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            throw ApiException::serverError('Failed to create company');
        }
    }

    /**
     * Update company
     */
    public function updateCompany(int $id, array $data): Company
    {
        DB::beginTransaction();

        try {
            $company = $this->getCompanyById($id);

            // Handle logo upload using helper
            if (isset($data['logo'])) {
                $data['logo'] = FileUploadHelper::replace(
                    $data['logo'],
                    $company->logo,
                    'companies/logos'
                );
            }

            $company->update($data);
            LogHelper::updated('company', $company->id, $company->id);

            DB::commit();

            Log::info('Company updated successfully', ['company_id' => $company->id]);

            return $company->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete new uploaded logo if exists
            if (isset($data['logo'])) {
                FileUploadHelper::delete($data['logo']);
            }

            Log::error('Company update failed: ' . $e->getMessage(), [
                'company_id' => $id,
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            throw ApiException::serverError('Failed to update company');
        }
    }
    public function storeUpdateRequest(int $id, array $data): Company
    {
        DB::beginTransaction();

        try {
            $company = Company::findOrFail($id);
            if (isset($data['logo'])) {
                $data['logo'] = FileUploadHelper::uploadImage(
                    $data['logo'],
                    'companies/logos',

                );
            }
            $company->updateRequests()->create($data);

            LogHelper::updated('company', $company->id, $company->id, $company->name . "request for update");

            DB::commit();

            Log::info('Company updated successfully', ['company_id' => $company->id]);

            return $company->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete new uploaded logo if exists
            if (isset($data['logo'])) {
                FileUploadHelper::delete($data['logo']);
            }

            Log::error('Company update failed: ' . $e->getMessage(), [
                'company_id' => $id,
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            throw ApiException::serverError('Failed to update company');
        }
    }
    public function updateUpdateRequest(int $id, array $data): CompanyUpdateRequest
    {
        DB::beginTransaction();

        try {
            $companyReq = CompanyUpdateRequest::find($id);

            if ($companyReq == Status::Approved->value || $companyReq->status == Status::Rejected->value) {
                throw ApiException::notFound('Cannot update this request');
            }
            if (isset($data['logo'])) {
                $data['logo'] = FileUploadHelper::replace(
                    $data['logo'],
                    $companyReq->logo,
                    'companies/logos'
                );
            }
            $companyReq->update($data);


            DB::commit();

            Log::info('Company updated successfully', ['company_id' => $companyReq->id]);

            return $companyReq->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete new uploaded logo if exists
            if (isset($data['logo'])) {
                FileUploadHelper::delete($data['logo']);
            }

            Log::error('Company update failed: ' . $e->getMessage(), [
                'company_id' => $id,
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            throw ApiException::serverError('Failed to update company');
        }
    }

    /**
     * Delete company (soft delete)
     */
    public function deleteCompany(int $id): bool
    {
        try {
            $company = $this->getCompanyById($id);

            $company->delete();
            LogHelper::deleted('company', $id, $id);

            Log::info('Company deleted successfully', ['company_id' => $id]);

            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Company deletion failed: ' . $e->getMessage(), [
                'company_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            throw ApiException::serverError('Failed to delete company');
        }
    }

    /**
     * Restore soft deleted company
     */
    public function restoreCompany(int $id): Company
    {
        try {
            $company = Company::withTrashed()->find($id);

            if (!$company) {
                throw ApiException::notFound('Company');
            }

            $company->restore();
            LogHelper::restored('company', $id, $company->id);

            Log::info('Company restored successfully', ['company_id' => $id]);

            return $company;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Company restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore company');
        }
    }

    /**
     * Permanently delete company
     */
    public function forceDeleteCompany(int $id): bool
    {
        DB::beginTransaction();

        try {
            $company = Company::withTrashed()->find($id);

            if (!$company) {
                throw ApiException::notFound('Company');
            }

            // Delete logo using helper
            FileUploadHelper::delete($company->logo);

            $company->forceDelete();
            LogHelper::forceDeleted('company', $id, $id);

            DB::commit();

            Log::info('Company permanently deleted', ['company_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Company permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete company');
        }
    }

    /**
     * Toggle company status
     */
    public function toggleStatus(int $id): Company
    {
        try {
            $company = $this->getCompanyById($id);
            $company->update(['status' => !$company->status]);
            LogHelper::statusChanged('company', $id, $company->id);

            Log::info('Company status toggled', [
                'company_id' => $id,
                'new_status' => $company->status
            ]);

            return $company;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Company status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle company status');
        }
    }

    /**
     * Get companies by business type
     */
    public function getCompaniesByBusinessType(string $type): Collection
    {
        return Company::byBusinessType($type)->get();
    }

    /**
     * Get active companies
     */
    public function getActiveCompanies(): Collection
    {
        return Company::active()->get();
    }

    /**
     * Search companies
     */
    public function searchCompanies(string $term): Collection
    {
        return Company::where('name', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
            ->orWhere('phone', 'like', "%{$term}%")
            ->get();
    }
    /**
     * Get full company profile
     */
    public function getCompanyProfileById(int $id, string $period = 'month'): Company
    {
        $startDate = match ($period) {
            'week'  => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year'  => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $company = Company::withCount([
            // Total counts
            'users',
            'orders',
            // New this period
            'users as new_users_count'   => fn($q) => $q->where('created_at', '>=', $startDate),
            'orders as new_orders_count' => fn($q) => $q->where('created_at', '>=', $startDate),
            // User status
            'users as active_users_count'   => fn($q) => $q->where('status', Status::Active->value),
            'users as inactive_users_count' => fn($q) => $q->where('status', Status::Inactive->value),
            // Order status
            'orders as pending_orders'   => fn($q) => $q->where('status', Status::Pending->value),
            'orders as completed_orders' => fn($q) => $q->where('status', Status::Completed->value),
            'orders as cancelled_orders' => fn($q) => $q->where('status', Status::Cancelled->value),
        ])
            ->find($id);

        if (!$company) {
            throw ApiException::notFound('Company not found');
        }

        // Revenue calculation
        $company->total_revenue = $company->orders()->sum('grand_total');
        $company->new_revenue   = $company->orders()
            ->where('created_at', '>=', $startDate)
            ->sum('grand_total');

        return $company;
    }
}
