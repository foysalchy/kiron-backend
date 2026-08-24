<?php

namespace App\Services;

use App\Models\Company;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\{DB, Log, Storage};

class CompanyDeletionService
{
    protected array $relations = [
        'users',
        'orderReturns',
        'orders',
        'purchaseReturns',
        'purchases',
        'requisitions',
        'coupons',
        'products',
        'parties',
        'warehouses',
    ];

    protected array $imageSources = [
        [
            'model'   => \App\Models\Company::class,
            'columns' => ['logo'],
            'scope'   => 'self',
        ],
        [
            'model'   => \App\Models\User::class,
            'columns' => ['profile'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\Party::class,
            'columns' => ['profile'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\Brand::class,
            'columns' => ['logo'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\MegaCategory::class,
            'columns' => ['image'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\SubCategory::class,
            'columns' => ['image'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\MiniCategory::class,
            'columns' => ['image'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\ExtraCategory::class,
            'columns' => ['image'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\Product::class,
            'columns' => ['thumbnail'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\Gallery::class,
            'columns' => ['image'],
            'scope'   => 'via_product',
        ],
        [
            'model'   => \App\Models\ProductVariation::class,
            'columns' => ['image'],
            'scope'   => 'via_product',
        ],
        [
            'model'   => \App\Models\VariationGallery::class,
            'columns' => ['image'],
            'scope'   => 'via_variation',
        ],
        [
            'model'   => \App\Models\Page::class,
            'columns' => ['image'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\Slider::class,
            'columns' => ['image'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\Blog::class,
            'columns' => ['images'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\Employee::class,
            'columns' => ['image'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\Resignation::class,
            'columns' => ['letter'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\Rejoin::class,
            'columns' => ['appointment_letter'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\LeaveApplication::class,
            'columns' => ['documents'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\LandingPage::class,
            'columns' => ['thumbnail'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\LandingPage::class,
            'columns' => ['video'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\LandingPage::class,
            'columns' => ['img_paths'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\SiteSetting::class,
            'columns' => ['logo'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\CustomerPaymentMethod::class,
            'columns' => ['icon'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\SupportTicket::class,
            'columns' => ['image'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\TransactionTransfer::class,
            'columns' => ['file'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\Asset::class,
            'columns' => ['image'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\SocialSetting::class,
            'columns' => ['icon_image'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\ContentSetting::class,
            'columns' => ['icon_url'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\ProductReview::class,
            'columns' => ['images'],
            'scope'   => 'company_id',
        ],
        [
            'model'   => \App\Models\CompanyUpdateRequest::class,
            'columns' => ['logo'],
            'scope'   => 'company_id',
        ],
    ];

    protected function usesSoftDeletes(string $modelClass): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($modelClass));
    }

    protected function getBaseQuery(string $modelClass)
    {
        $query = $this->usesSoftDeletes($modelClass)
            ? $modelClass::withTrashed()
            : $modelClass::query();

        return $query->withoutGlobalScopes();
    }


    protected function deleteQuery($query, string $modelClass): int
    {
        return $this->usesSoftDeletes($modelClass)
            ? $query->forceDelete()
            : $query->delete();
    }

 
    protected function getCleanPath($path): ?string
    {
        if (!$path) {
            return null;
        }
        
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $parsedUrl = parse_url($path);
            $path = $parsedUrl['path'] ?? $path;
        }
        
        return ltrim($path, '/');
    }


    protected function deleteCompanyImages(Company $company): int
    {
        $deletedCount = 0;
        $companyId = $company->id;

        $productIds = $this->getBaseQuery(\App\Models\Product::class)
            ->where('company_id', $companyId)
            ->pluck('id')
            ->all();

        $variationIds = $this->getBaseQuery(\App\Models\ProductVariation::class)
            ->whereIn('product_id', $productIds)
            ->pluck('id')
            ->all();

        foreach ($this->imageSources as $source) {
            $modelClass = $source['model'];
            $columns    = $source['columns'];
            $scope      = $source['scope'];

            if (!class_exists($modelClass)) {
                Log::warning("Model class does not exist under this namespace. Skipping.", [
                    'model' => $modelClass
                ]);
                continue;
            }

            if (empty($columns)) {
                continue;
            }

            $query = $this->getBaseQuery($modelClass);

            match ($scope) {
                'self'          => $query->where('id', $companyId),
                'company_id'    => $query->where('company_id', $companyId),
                'via_product'   => $query->whereIn('product_id', $productIds),
                'via_variation' => $query->whereIn('variation_id', $variationIds),
                default         => null,
            };

            $rows = $query->get($columns);

            Log::info("Image source query executed", [
                'model' => $modelClass,
                'records_found' => $rows->count()
            ]);

            foreach ($rows as $row) {
                foreach ($columns as $col) {
                    $value = method_exists($row, 'getRawOriginal') 
                        ? $row->getRawOriginal($col) 
                        : $row->{$col};

                    if (!$value) {
                        continue;
                    }

                    $paths = [];
                    if (is_array($value)) {
                        $paths = $value;
                    } elseif (is_string($value)) {
                        $decoded = json_decode($value, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $paths = $decoded;
                        } else {
                            $paths = [$value];
                        }
                    }

                    foreach ($paths as $path) {
                        $cleanPath = $this->getCleanPath($path);

                        if ($cleanPath) {
                            Log::info("Attempting to delete R2 file", [
                                'model'  => $modelClass,
                                'column' => $col,
                                'path'   => $cleanPath
                            ]);

                            try {
                                if (Storage::disk('r2')->delete($cleanPath)) {
                                    $deletedCount++;
                                    Log::info("Successfully deleted R2 file", ['path' => $cleanPath]);
                                } else {
                                    Log::warning("R2 deletion returned false for file", ['path' => $cleanPath]);
                                }
                            } catch (\Throwable $e) {
                                Log::error("Failed to delete R2 file due to Exception", [
                                    'path'  => $cleanPath,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                    }
                }
            }
        }

        Log::info('Deleted R2 images for company', [
            'company_id' => $company->id,
            'files_deleted' => $deletedCount,
        ]);

        return $deletedCount;
    }

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

            $company->delete();

            DB::commit();

            Log::info('Company soft deleted successfully', [
                'company_id' => $company->id,
                'deleted_counts' => $deletedCounts
            ]);

            LogHelper::deleted('company', $company->id, $company->id, 'soft delete ' . $company->name);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Company soft deletion failed', [
                'company_id' => $company->id ?? $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw ApiException::serverError(
                'Failed to delete company: ' . $e->getMessage()
            );
        }
    }


    public function forceDelete($id): bool
    {
        DB::beginTransaction();

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $company = Company::withTrashed()->findOrFail($id);

            Log::info('Starting force delete for company', [
                'company_id' => $company->id,
                'company_name' => $company->name,
            ]);

            $filesDeleted = $this->deleteCompanyImages($company);

            $companyId   = $company->id;
            $companyName = $company->name;

            $totalDeletedRows = 0;

            $productIds = $this->getBaseQuery(\App\Models\Product::class)
                ->where('company_id', $companyId)
                ->pluck('id')
                ->all();

            if (!empty($productIds)) {
                // Variation Galleries
                $variationIds = $this->getBaseQuery(\App\Models\ProductVariation::class)
                    ->whereIn('product_id', $productIds)
                    ->pluck('id')
                    ->all();

                if (!empty($variationIds)) {
                    $vgQuery = $this->getBaseQuery(\App\Models\VariationGallery::class)->whereIn('variation_id', $variationIds);
                    $totalDeletedRows += $this->deleteQuery($vgQuery, \App\Models\VariationGallery::class);
                }
                // Product Variations
                $pvQuery = $this->getBaseQuery(\App\Models\ProductVariation::class)->whereIn('product_id', $productIds);
                $totalDeletedRows += $this->deleteQuery($pvQuery, \App\Models\ProductVariation::class);
                
                // Product Galleries
                $gQuery = $this->getBaseQuery(\App\Models\Gallery::class)->whereIn('product_id', $productIds);
                $totalDeletedRows += $this->deleteQuery($gQuery, \App\Models\Gallery::class);
            }

            $orderIds = $this->getBaseQuery(\App\Models\Order::class)
                ->where('company_id', $companyId)
                ->pluck('id')
                ->all();

            if (!empty($orderIds)) {
                $totalDeletedRows += DB::table('order_details')->whereIn('order_id', $orderIds)->delete();
            }

            $purchaseIds = $this->getBaseQuery(\App\Models\Purchase::class)
                ->where('company_id', $companyId)
                ->pluck('id')
                ->all();

            if (!empty($purchaseIds)) {
                $totalDeletedRows += DB::table('purchase_details')->whereIn('purchase_id', $purchaseIds)->delete();
            }

            $companyModels = [
                \App\Models\User::class,
                \App\Models\Party::class,
                \App\Models\Brand::class,
                \App\Models\MegaCategory::class,
                \App\Models\SubCategory::class,
                \App\Models\MiniCategory::class,
                \App\Models\ExtraCategory::class,
                \App\Models\Product::class,
                \App\Models\Page::class,
                \App\Models\Slider::class,
                \App\Models\Blog::class,
                \App\Models\Employee::class,
                \App\Models\Resignation::class,
                \App\Models\Rejoin::class,
                \App\Models\LeaveApplication::class,
                \App\Models\LandingPage::class,
                \App\Models\SiteSetting::class,
                \App\Models\CustomerPaymentMethod::class,
                \App\Models\SupportTicket::class,
                \App\Models\TransactionTransfer::class,
                \App\Models\Asset::class,
                \App\Models\SocialSetting::class,
                \App\Models\ContentSetting::class,
                \App\Models\ProductReview::class,
                \App\Models\CompanyUpdateRequest::class,
                \App\Models\Order::class,
                \App\Models\OrderReturn::class,
                \App\Models\Purchase::class,
                \App\Models\PurchaseReturn::class,
                \App\Models\Requisition::class,
                \App\Models\Coupon::class,
                \App\Models\Warehouse::class,
            ];

            foreach ($companyModels as $modelClass) {
                if (!class_exists($modelClass)) {
                    continue;
                }
                try {
                    $query = $this->getBaseQuery($modelClass)->where('company_id', $companyId);
                    
                    $count = $this->deleteQuery($query, $modelClass);
                    $totalDeletedRows += $count;
                } catch (\Throwable $e) {
                    Log::warning("Could not force delete model {$modelClass}", [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $totalDeletedRows += DB::table('action_logs')->where('company_id', $companyId)->delete();

            DB::table('companies')->where('id', $companyId)->delete();

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            DB::commit();

            Log::info('Company force deleted successfully', [
                'company_id' => $companyId,
                'files_deleted' => $filesDeleted,
                'related_rows_deleted' => $totalDeletedRows,
            ]);

            LogHelper::custom(
                'force_deleted',
                'company',
                $companyId,
                null, 
                "Permanently deleted company {$companyName} ({$filesDeleted} files removed from R2, {$totalDeletedRows} related rows removed)"
            );

            return true;
        } catch (\Throwable $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::rollBack();

            Log::error('Company force deletion failed', [
                'company_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw ApiException::serverError(
                'Failed to permanently delete company: ' . $e->getMessage()
            );
        }
    }

    /**
     * Restore soft deleted company and all related data (ডাইনামিক হ্যান্ডেলিং সহ)
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

            $company->restore();

            foreach (array_reverse($this->relations) as $relation) {
                try {
                    if (method_exists($company, $relation)) {
                        $relationQuery = $company->$relation();
                        $relatedModel  = $relationQuery->getRelated();
                        $relatedClass  = get_class($relatedModel);

                        // শুধুমাত্র SoftDeletes সমর্থন করলেই কেবল রিস্টোর কোড রান করবে
                        if ($this->usesSoftDeletes($relatedClass)) {
                            $count = $relationQuery->onlyTrashed()->count();

                            if ($count > 0) {
                                $relationQuery->onlyTrashed()->restore();
                                $restoredCounts[$relation] = $count;

                                Log::info("Restored {$relation}", [
                                    'company_id' => $company->id,
                                    'count' => $count
                                ]);
                            }
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
                }
            }

            DB::commit();

            Log::info('Company restored successfully', [
                'company_id' => $company->id,
                'restored_counts' => $restoredCounts
            ]);

            LogHelper::custom('restored', 'company', $company->id, $company->id, 'restore company ' . $company->name);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Company restoration failed', [
                'company_id' => $company->id ?? $id,
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
                'company_id' => $id,
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

            if ($company->orders()->where('status', 0)->exists()) {
                $blockers[] = 'Company has pending orders';
            }

            if ($company->users()->where('status', 1)->exists()) {
                $blockers[] = 'Company has active users';
            }

            return [
                'can_delete' => empty($blockers),
                'blockers' => $blockers,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to check deletion eligibility', [
                'company_id' => $id,
                'error' => $e->getMessage()
            ]);

            throw ApiException::serverError(
                'Failed to check deletion eligibility: ' . $e->getMessage()
            );
        }
    }
}