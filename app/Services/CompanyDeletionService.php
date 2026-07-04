<?php

namespace App\Services;

use App\Models\Company;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\{DB, Log, Storage};

class CompanyDeletionService
{
    // softDelete এবং ডিলিট সামারির জন্য রিলেশনগুলোর তালিকা
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

    // R2 থেকে ফাইল ডিলিট করার সোর্সগুলো
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

    /**
     * ইমেজের ফুল URL থেকে রিলেটিভ পাথ আলাদা করার হেল্পার
     */
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

    /**
     * R2 ফাইল মুছে ফেলার ডিটেইলড লগিং সহ সংশোধিত মেথড
     */
    protected function deleteCompanyImages(Company $company): int
    {
        $deletedCount = 0;

        $productIds = \App\Models\Product::withTrashed()
            ->withoutGlobalScopes() // গ্লোবাল স্কোপ এড়ানো হচ্ছে
            ->where('company_id', $company->id)
            ->pluck('id');

        foreach ($this->imageSources as $source) {
            $modelClass = $source['model'];
            $columns    = $source['columns'];
            $scope      = $source['scope'];

            // যদি মডেল ক্লাসটির কোনো অস্তিত্ব না থাকে, তবে লগে ওয়ার্নিং দিয়ে স্কিপ করা হচ্ছে
            if (!class_exists($modelClass)) {
                Log::warning("Model class does not exist under this namespace. Skipping.", [
                    'model' => $modelClass
                ]);
                continue;
            }

            if (empty($columns)) {
                continue;
            }

            $query = $this->usesSoftDeletes($modelClass)
                ? $modelClass::withTrashed()
                : $modelClass::query();

            // গ্লোবাল স্কোপ (TenantScope/CompanyScope) যাতে কোয়েরিকে বাধা না দেয়
            $query->withoutGlobalScopes();

            match ($scope) {
                'self'        => $query->where('id', $company->id),
                'company_id'  => $query->where('company_id', $company->id),
                'via_product' => $query->whereIn('product_id', $productIds),
                default       => null,
            };

            $rows = $query->get($columns);

            // ট্র্যাকিংয়ের জন্য লগে কোয়েরি রেজাল্ট কাউন্ট প্রিন্ট করা হচ্ছে
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

    /**
     * Soft Delete (কোম্পানি এবং সম্পর্কিত মূল রিলেশনগুলো)
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

    /**
     * Force Delete (সম্পূর্ণ মডেল এবং রিলেশন ভিত্তিক - গ্লোবাল স্কোপ এড়ানো সহ)
     */
    public function forceDelete($id): bool
    {
        DB::beginTransaction();

        try {
            // ১. সাময়িকভাবে ফরেন কি চেক বন্ধ করা হচ্ছে
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $company = Company::withTrashed()->findOrFail($id);

            Log::info('Starting force delete for company', [
                'company_id' => $company->id,
                'company_name' => $company->name,
            ]);

            // ২. R2 ফাইলগুলো ডিলিট করা হচ্ছে
            $filesDeleted = $this->deleteCompanyImages($company);

            $companyId   = $company->id;
            $companyName = $company->name;

            $totalDeletedRows = 0;

            // ৩. চাইল্ড/নেস্টেড মডেলগুলোর ডাটা আগে মুছে ফেলা হচ্ছে
            $productIds = \App\Models\Product::withTrashed()
                ->withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->pluck('id')
                ->all();

            if (!empty($productIds)) {
                // Variation Galleries
                $variationIds = \App\Models\ProductVariation::withTrashed()
                    ->withoutGlobalScopes()
                    ->whereIn('product_id', $productIds)
                    ->pluck('id')
                    ->all();

                if (!empty($variationIds)) {
                    $totalDeletedRows += \App\Models\VariationGallery::withTrashed()
                        ->withoutGlobalScopes()
                        ->whereIn('variation_id', $variationIds)
                        ->forceDelete();
                }
                // Product Variations
                $totalDeletedRows += \App\Models\ProductVariation::withTrashed()
                    ->withoutGlobalScopes()
                    ->whereIn('product_id', $productIds)
                    ->forceDelete();
                // Product Galleries
                $totalDeletedRows += \App\Models\Gallery::withTrashed()
                    ->withoutGlobalScopes()
                    ->whereIn('product_id', $productIds)
                    ->forceDelete();
            }

            // অর্ডারের চাইল্ড আইটেম ডিলিট
            $orderIds = \App\Models\Order::withTrashed()
                ->withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->pluck('id')
                ->all();

            if (!empty($orderIds)) {
                $totalDeletedRows += DB::table('order_items')->whereIn('order_id', $orderIds)->delete();
                $totalDeletedRows += DB::table('order_details')->whereIn('order_id', $orderIds)->delete();
            }

            // পারচেজের চাইল্ড আইটেম ডিলিট
            $purchaseIds = \App\Models\Purchase::withTrashed()
                ->withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->pluck('id')
                ->all();

            if (!empty($purchaseIds)) {
                $totalDeletedRows += DB::table('purchase_items')->whereIn('purchase_id', $purchaseIds)->delete();
                $totalDeletedRows += DB::table('purchase_details')->whereIn('purchase_id', $purchaseIds)->delete();
            }

            // ৪. সমস্ত প্রাইমারি মডেল যা সরাসরি কোম্পানির আইডি ধারণ করে (গ্লোবাল স্কোপ এড়ানো সহ)
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
                    $query = $this->usesSoftDeletes($modelClass)
                        ? $modelClass::withTrashed()
                        : $modelClass::query();
                        
                    // গ্লোবাল স্কোপ এড়ানো হচ্ছে
                    $query->withoutGlobalScopes();

                    $count = $query->where('company_id', $companyId)->forceDelete();
                    $totalDeletedRows += $count;
                } catch (\Throwable $e) {
                    Log::warning("Could not force delete model {$modelClass}", [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // ৫. কোম্পানির পুরনো অ্যাকশন লগগুলো ডিলিট করা
            $totalDeletedRows += DB::table('action_logs')->where('company_id', $companyId)->delete();

            // ৬. কোম্পানির মূল রেকর্ড ডিলিট করা
            DB::table('companies')->where('id', $companyId)->delete();

            // ৭. ফরেন কি চেক পুনরায় চালু করা হচ্ছে
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            DB::commit();

            Log::info('Company force deleted successfully', [
                'company_id' => $companyId,
                'files_deleted' => $filesDeleted,
                'related_rows_deleted' => $totalDeletedRows,
            ]);

            // সিস্টেম ট্র্যাকিংয়ের জন্য অ্যাকশন লগ তৈরি
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

            $company->restore();

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