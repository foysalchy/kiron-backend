<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Company;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompanyService
{

    public function getAllCompanies(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Company::query();

            // Apply filters
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
                    $q->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('email', 'like', "%{$filters['search']}%")
                        ->orWhere('phone', 'like', "%{$filters['search']}%");
                });
            }

            // Sorting
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            // Return paginated or all
            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
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
        $company = Company::find($id);

        if (!$company) {
            throw ApiException::notFound('Company');
        }

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
                    'public',
                    2048 // 2MB max
                );
            }

            $company = Company::create($data);
            LogHelper::created('company', $company->id, $company->id);


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
}
