<?php

namespace App\Services;

use App\Models\Company;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\{DB, Log};

class CompanyDeletionService
{
    /**
     * Relations to be deleted with company
     * Order matters: delete children before parents
     */
    protected array $relations = [
        'orderReturns',
        'orders',
        'purchaseReturns',
        'purchases',
        'requisitions',
        'coupons',
        'products',
        'parties',
        'warehouses',
        // Add other relations here...
    ];

    /**
     * Soft delete company and all related data
     */
    public function softDelete(int $id): bool
    {
        DB::beginTransaction();

        try {
            $company = Company::findOrFail($id);
            Log::info('Starting soft delete for company', [
                'company_id' => $company->id,
                'company_name' => $company->name
            ]);

            $deletedCounts = [];

            // Soft delete all related records
            foreach ($this->relations as $relation) {
                try {
                    if (method_exists($company, $relation)) {
                        $count = $company->$relation()->count();

                        if ($count > 0) {
                            $company->$relation()->delete();
                            $deletedCounts[$relation] = $count;

                            Log::info("Soft deleted {$relation}", [
                                'company_id' => $company->id,
                                'count' => $count
                            ]);
                        }
                    } else {
                        Log::warning("Relation method not found", [
                            'company_id' => $company->id,
                            'relation' => $relation
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to soft delete relation", [
                        'company_id' => $company->id,
                        'relation' => $relation,
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                }
            }

            // Soft delete company
            $company->delete();

            DB::commit();

            Log::info('Company soft deleted successfully', [
                'company_id' => $company->id,
                'deleted_counts' => $deletedCounts
            ]);

            LogHelper::deleted('company', $company->id, $company->id);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Company soft deletion failed', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw ApiException::serverError(
                'Failed to delete company: ' . $e->getMessage()
            );
        }
    }

    /**
     * Permanently delete company and all related data
     */
    public function forceDelete($id): bool
    {
        DB::beginTransaction();

        try {
            $company = Company::withTrashed()->findOrFail($id);
            Log::info('Starting force delete for company', [
                'company_id' => $company->id,
                'company_name' => $company->name
            ]);

            $deletedCounts = [];

            // Force delete all related records (including soft deleted)
            foreach ($this->relations as $relation) {
                try {
                    if (method_exists($company, $relation)) {
                        $count = $company->$relation()->withTrashed()->count();

                        if ($count > 0) {
                            $company->$relation()->withTrashed()->forceDelete();
                            $deletedCounts[$relation] = $count;

                            Log::info("Force deleted {$relation}", [
                                'company_id' => $company->id,
                                'count' => $count
                            ]);
                        }
                    } else {
                        Log::warning("Relation method not found", [
                            'company_id' => $company->id,
                            'relation' => $relation
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to force delete relation", [
                        'company_id' => $company->id,
                        'relation' => $relation,
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                }
            }

            // Force delete company
            $companyId = $company->id;
            $company->forceDelete();

            DB::commit();

            Log::info('Company force deleted successfully', [
                'company_id' => $companyId,
                'deleted_counts' => $deletedCounts
            ]);

            LogHelper::custom('force_deleted', 'company', $companyId, $companyId);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Company force deletion failed', [
                'company_id' => $company->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw ApiException::serverError(
                'Failed to permanently delete company: ' . $e->getMessage()
            );
        }
    }

    /**
     * Restore soft deleted company and all related data
     */
    public function restore($id): bool
    {
        DB::beginTransaction();

        try {
            $company = Company::onlyTrashed()->findOrFail($id);
            Log::info('Starting restore for company', [
                'company_id' => $company->id,
                'company_name' => $company->name
            ]);

            $restoredCounts = [];

            // Restore company first
            $company->restore();

            // Restore all related records
            foreach (array_reverse($this->relations) as $relation) {
                try {
                    if (method_exists($company, $relation)) {
                        $count = $company->$relation()->onlyTrashed()->count();

                        if ($count > 0) {
                            $company->$relation()->onlyTrashed()->restore();
                            $restoredCounts[$relation] = $count;

                            Log::info("Restored {$relation}", [
                                'company_id' => $company->id,
                                'count' => $count
                            ]);
                        }
                    } else {
                        Log::warning("Relation method not found", [
                            'company_id' => $company->id,
                            'relation' => $relation
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to restore relation", [
                        'company_id' => $company->id,
                        'relation' => $relation,
                        'error' => $e->getMessage()
                    ]);
                    // Continue with other relations even if one fails
                }
            }

            DB::commit();

            Log::info('Company restored successfully', [
                'company_id' => $company->id,
                'restored_counts' => $restoredCounts
            ]);

            LogHelper::custom('restored', 'company', $company->id, $company->id);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Company restoration failed', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw ApiException::serverError(
                'Failed to restore company: ' . $e->getMessage()
            );
        }
    }

    /**
     * Get deletion summary (counts before deletion)
     */
    public function getDeletionSummary(int $id): array
    {
        try {
            $company = Company::findOrFail($id);
            $summary = [
                'company_id' => $company->id,
                'company_name' => $company->name,
                'relations' => [],
                'total_records' => 0,
            ];

            foreach ($this->relations as $relation) {
                if (method_exists($company, $relation)) {
                    $count = $company->$relation()->count();

                    if ($count > 0) {
                        $summary['relations'][$relation] = $count;
                        $summary['total_records'] += $count;
                    }
                }
            }

            return $summary;
        } catch (\Exception $e) {
            Log::error('Failed to get deletion summary', [
                'company_id' => $company->id,
                'error' => $e->getMessage()
            ]);

            throw ApiException::serverError(
                'Failed to get deletion summary: ' . $e->getMessage()
            );
        }
    }

    /**
     * Check if company can be safely deleted
     */
    public function canDelete(int $id): array
    {
        try {
            $company = Company::findOrFail($id);
            $blockers = [];

            // Check for active orders
            if ($company->orders()->where('status', 0)->exists()) {
                $blockers[] = 'Company has pending orders';
            }

            // Check for active users
            if ($company->users()->where('status', 1)->exists()) {
                $blockers[] = 'Company has active users';
            }

            // Add more business rules here...

            return [
                'can_delete' => empty($blockers),
                'blockers' => $blockers,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to check deletion eligibility', [
                'company_id' => $company->id,
                'error' => $e->getMessage()
            ]);

            throw ApiException::serverError(
                'Failed to check deletion eligibility: ' . $e->getMessage()
            );
        }
    }
}
