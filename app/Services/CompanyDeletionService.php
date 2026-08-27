<?php

namespace App\Services;

use App\Models\Company;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\DomainSetup;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\{Cache, DB, Log, Storage};

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

    public static function getCompanyFolder(int $companyId): string
    {
        $prefix = self::getCompanyPrefix($companyId);
        return "{$prefix}_{$companyId}";
    }
    protected static function getCompanyPrefix(int $companyId): string
    {
        return Cache::rememberForever("domain_prefix_company_{$companyId}", function () use ($companyId) {
            $domainSetup = DomainSetup::where('company_id', $companyId)->first();
            return $domainSetup->prefix ?? 'default';
        });
    }
    /**
     * Company force delete হওয়ার সময় R2 থেকে পুরো company folder (সব sub-folder/file সহ) delete করে
     */
    public static function deleteCompanyFolder(int $companyId, string $disk = 'r2'): bool
    {
        try {
            $companyFolder = self::getCompanyFolder($companyId);

            Log::info('Attempting to delete R2 company folder', [
                'company_id' => $companyId,
                'folder'     => $companyFolder,
            ]);

            // deleteDirectory স্বয়ংক্রিয়ভাবে ওই prefix-এর নিচের সব file/sub-folder রিকার্সিভলি delete করে
            $deleted = Storage::disk($disk)->deleteDirectory($companyFolder);

            if ($deleted) {
                Log::info('Successfully deleted R2 company folder', [
                    'company_id' => $companyId,
                    'folder'     => $companyFolder,
                ]);
            } else {
                Log::warning('R2 folder deletion returned false', [
                    'company_id' => $companyId,
                    'folder'     => $companyFolder,
                ]);
            }

            return $deleted;
        } catch (\Throwable $e) {
            Log::error('Failed to delete R2 company folder due to Exception', [
                'company_id' => $companyId,
                'error'      => $e->getMessage(),
            ]);
            return false;
        }
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

            $filesDeleted = $this->deleteCompanyFolder($company->id);

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
                \App\Models\AccountGroup::class,
       
                \App\Models\ActionLog::class,
                \App\Models\AiSetting::class,
                \App\Models\Area::class,
                \App\Models\Asset::class,
                \App\Models\AssetCategory::class,
                \App\Models\AssetDepreciation::class,
                \App\Models\AssetDisposal::class,
                \App\Models\AssetPurchase::class,
                \App\Models\AssignLeaveType::class,
                \App\Models\Attendance::class,
                \App\Models\AttributeGroup::class,
                \App\Models\AttributeValue::class,
                \App\Models\BarCode::class,
                \App\Models\Billing::class,
                \App\Models\Bin::class,
                \App\Models\Blog::class,
                \App\Models\Bonus::class,
                \App\Models\Brand::class,
                \App\Models\Cart::class,
                \App\Models\Cell::class,
                \App\Models\Channel::class,
                \App\Models\ChannelConnection::class,
                \App\Models\ChannelGroup::class,
                \App\Models\ChartOfAccount::class,

                \App\Models\CompanyUpdateRequest::class,
                \App\Models\ContactMessage::class,
                \App\Models\ContentSetting::class,
                \App\Models\Conversation::class,
                \App\Models\Coupon::class,

                \App\Models\Courier::class,
                \App\Models\CourierCheckHistory::class,
                \App\Models\CourierMethod::class,
        
                \App\Models\Currency::class,
                \App\Models\CustomerGroup::class,
                \App\Models\CustomerPaymentMethod::class,
       
                \App\Models\Department::class,
                \App\Models\DisposalType::class,
                \App\Models\Domain::class,
                \App\Models\DomainSetup::class,
                \App\Models\EmailSend::class,
                \App\Models\EmailSetting::class,
                \App\Models\EmailTemplate::class,
                \App\Models\EmailVerification::class,
                \App\Models\Employee::class,
                \App\Models\EmployeeSalary::class,
                \App\Models\EmployeeType::class,
                \App\Models\ExtraCategory::class,
                \App\Models\ExtraOrderCharge::class,
                \App\Models\FirebaseSetting::class,
                \App\Models\FooterCode::class,
    
                \App\Models\GeneratePayslip::class,
                \App\Models\Holiday::class,
                \App\Models\InventoryAudit::class,
    
                \App\Models\IpDirectory::class,
                \App\Models\IpSetting::class,
                \App\Models\JobTitle::class,
                \App\Models\KnowledgeBase::class,
                \App\Models\Label::class,
                \App\Models\LandingPage::class,
                \App\Models\Lead::class,
                \App\Models\LeadNote::class,
                \App\Models\LeadSource::class,
                \App\Models\LeadStatus::class,
                \App\Models\LeaveApplication::class,
                \App\Models\LeaveType::class,
                \App\Models\Market::class,

                \App\Models\MegaCategory::class,
                \App\Models\MenuSetting::class,

                \App\Models\MiniCategory::class,
                \App\Models\NoteTemplate::class,
                \App\Models\OfficeLocation::class,
                \App\Models\OmniSetting::class,
                \App\Models\Order::class,
        
                \App\Models\OrderNote::class,
      
                \App\Models\OrderReturn::class,
        
                \App\Models\Page::class,
                \App\Models\Party::class,
                \App\Models\PayHead::class,
                \App\Models\PayRoll::class,
                \App\Models\PayRollPayHead::class,
                \App\Models\PaySlipManager::class,
                \App\Models\PaymentMethodType::class,
                \App\Models\PayrollPeriod::class,
                \App\Models\PayrollSetting::class,
                \App\Models\Payslip::class,
                \App\Models\PayslipItem::class,
                \App\Models\Period::class,
                \App\Models\PeriodType::class,

                \App\Models\Position::class,
                \App\Models\Product::class,
                \App\Models\ProductGroup::class,
                \App\Models\ProductReview::class,
                \App\Models\ProductStockLedger::class,
                \App\Models\ProductVariation::class,
                \App\Models\ProductVariationAttribute::class,
                \App\Models\ProductVariationStock::class,
                \App\Models\ProductVariationStockLedger::class,
                \App\Models\ProductView::class,
                \App\Models\Purchase::class,
                \App\Models\PurchasePayment::class,
                \App\Models\PurchaseReturn::class,
             
                \App\Models\QuikReply::class,
                \App\Models\Quotation::class,
                \App\Models\QuotationItem::class,
                \App\Models\Rack::class,
                \App\Models\RecurringJournal::class,
                \App\Models\Rejoin::class,
                \App\Models\ReminderLog::class,
                \App\Models\ReminderSetting::class,
                \App\Models\Requisition::class,
                \App\Models\RequisitionDetail::class,
                \App\Models\ResignRule::class,
                \App\Models\Resignation::class,
                \App\Models\Role::class,
                \App\Models\SearchProduct::class,
                \App\Models\SiteSetting::class,
                \App\Models\Slider::class,
                \App\Models\SmsSend::class,
                \App\Models\SmsTemplate::class,
                \App\Models\SmsWallet::class,
                \App\Models\SocialSetting::class,
                \App\Models\StatusMapping::class,
                \App\Models\StockAdjustment::class,
                \App\Models\StockAdjustmentItem::class,
                \App\Models\StockMovement::class,
                \App\Models\StockMovementItem::class,
                \App\Models\StockMovementRequest::class,
                \App\Models\StockMovementRequestItem::class,
                \App\Models\SubCategory::class,
                \App\Models\Subscription::class,
                \App\Models\SupportDepartment::class,
                \App\Models\SupportTicket::class,
                \App\Models\SupportTicketReply::class,
                \App\Models\SystemPage::class,
                \App\Models\TaxGroup::class,
                \App\Models\TaxRate::class,
                \App\Models\TransactionExpense::class,
                \App\Models\TransactionExpenseCategory::class,
                \App\Models\TransactionIncome::class,
                \App\Models\TransactionIncomeCategory::class,
                \App\Models\TransactionJournal::class,
                \App\Models\TransactionJournalAccount::class,
                \App\Models\TransactionTransfer::class,
                \App\Models\TransactionTransferDetails::class,
                \App\Models\UpdgradePackageRequest::class,
                \App\Models\User::class,
                \App\Models\UserLoginHistory::class,
                \App\Models\VariationGallery::class,
                \App\Models\Warehouse::class,
                \App\Models\Wishlist::class,
                \App\Models\WocommerceSetting::class,
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
