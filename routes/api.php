<?php


use App\Http\Controllers\Api\AccountGroupController;

use App\Http\Controllers\Api\ActionLogController;

use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\AssetCategoryController;
use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\AssetDepreciationController;
use App\Http\Controllers\Api\AssetDisposalController;
use App\Http\Controllers\Api\AssetPurchaseController;
use App\Http\Controllers\Api\AssignLeaveTypeController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AttributeGroupController;
use App\Http\Controllers\Api\AttributeValueController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BalanceSheetReportController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\BinController;
use App\Http\Controllers\Api\BkashController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\BonusController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\BulkActionController;
use App\Http\Controllers\Api\BusinessPaymentMethodController;
use App\Http\Controllers\Api\CellController;
use App\Http\Controllers\Api\ChannelController;
use App\Http\Controllers\Api\ChartOfAccountController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\SystemPageController;
use App\Http\Controllers\Api\CompanyRegistrationController;
use App\Http\Controllers\Api\ContentSettingController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\EmployeeTypeController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\CourierController;
use App\Http\Controllers\Api\CourierMethodController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\CustomerGroupController;
use App\Http\Controllers\Api\CustomerPanelController;
use App\Http\Controllers\Api\CustomerPaymentMethodController;
use App\Http\Controllers\Api\CustomerReportController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SetupProgressController;
use App\Http\Controllers\Api\DeliveryLocationController;
use App\Http\Controllers\Api\DisposalTypeController;
use App\Http\Controllers\Api\DomainSetupController;
use App\Http\Controllers\Api\EditorImagesController;
use App\Http\Controllers\Api\EmailSendController;
use App\Http\Controllers\Api\EmailSettingController;
use App\Http\Controllers\Api\EmailTemplateController;
use App\Http\Controllers\Api\EmployeeSalaryController;
use App\Http\Controllers\Api\ExtraCategoryController;
use App\Http\Controllers\Api\FirebaseSettingController;
use App\Http\Controllers\Api\FooterCodeController;
use App\Http\Controllers\Api\HolidayController;
use App\Http\Controllers\Api\InventoryAuditController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\InventoryStockReportController;
use App\Http\Controllers\Api\InventroyController;
use App\Http\Controllers\Api\IpDirectoryController;
use App\Http\Controllers\Api\IpSettingController;
use App\Http\Controllers\Api\JobTitleController;
use App\Http\Controllers\Api\KnowledgeBaseController;
use App\Http\Controllers\Api\LeaveTypeController;
use App\Http\Controllers\Api\LandingPageController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\LeadNoteController;
use App\Http\Controllers\Api\LeadSourceController;
use App\Http\Controllers\Api\LeadStatusController;
use App\Http\Controllers\Api\LeaveApplicationController;
use App\Http\Controllers\Api\LogActionController;
use App\Http\Controllers\Api\MarketController;
use App\Http\Controllers\Api\MegaCategoryController;
use App\Http\Controllers\Api\MenuSettingController;
use App\Http\Controllers\Api\MetaConnectController;
use App\Http\Controllers\Api\MetaWebhookController;
use App\Http\Controllers\Api\MetaDirectProxyController;
use App\Http\Controllers\Api\MiniCategoryController;
use App\Http\Controllers\Api\NoteTemplateController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OfficeLocationController;
use App\Http\Controllers\Api\OmniSettingsController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderNoteController;
use App\Http\Controllers\Api\OrderReturnController;
use App\Http\Controllers\Api\PackageUpgradeController;
use App\Http\Controllers\Api\PackageUsageController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\PartyController;
use App\Http\Controllers\Api\PassChangeController;
use App\Http\Controllers\Api\PathaoController;
use App\Http\Controllers\Api\PayHeadController;
use App\Http\Controllers\Api\PaymentCollectionController;
use App\Http\Controllers\Api\PaymentMethodTypeController;
use App\Http\Controllers\Api\PayRollController;
use App\Http\Controllers\Api\PayRollPayHeadController;
use App\Http\Controllers\Api\PayrollSettingController;
use App\Http\Controllers\Api\PayslipController;
use App\Http\Controllers\Api\PaySlipManagerController;
use App\Http\Controllers\Api\PeriodController;
use App\Http\Controllers\Api\PeriodTypeController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\PositionController;
use App\Http\Controllers\Api\PosOrderController;
use App\Http\Controllers\Api\PricingController;
use App\Http\Controllers\Api\PricingPackageController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductGroupController;
use App\Http\Controllers\Api\ProductWiseSalesReportController;
use App\Http\Controllers\Api\ProfitLossReportController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\PurchaseReturnController;
use App\Http\Controllers\Api\QuotationController;
use App\Http\Controllers\Api\RackController;
use App\Http\Controllers\Api\RecurringJournalController;
use App\Http\Controllers\Api\RejoinController;
use App\Http\Controllers\Api\ReminderSettingController;
use App\Http\Controllers\Api\ResignationController;
use App\Http\Controllers\Api\SlideController;
use App\Http\Controllers\Api\RequisitionController;
use App\Http\Controllers\Api\ResignRuleController;
use App\Http\Controllers\Api\ReferralGroupController;
use App\Http\Controllers\Api\ReferralPartnerController;
use App\Http\Controllers\Api\ReferralWithdrawalController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\Saas\CustomerReviewController;
use App\Http\Controllers\Api\Saas\MasterBrandController;
use App\Http\Controllers\Api\Saas\MasterDemoController;
use App\Http\Controllers\Api\Saas\MasterFeatureController;
use App\Http\Controllers\Api\SalesOrderController;
use App\Http\Controllers\Api\SalesReportController;
use App\Http\Controllers\Api\SelectOptionController;
use App\Http\Controllers\Api\SiteSettingController;
use App\Http\Controllers\Api\SmsPackageController;
use App\Http\Controllers\Api\SmsRechargeController;
use App\Http\Controllers\Api\SmsSendController;
use App\Http\Controllers\Api\SmsSettingController;
use App\Http\Controllers\Api\SmsTemplateController;
use App\Http\Controllers\Api\SmsWalletController;
use App\Http\Controllers\Api\SocialSettingController;
use App\Http\Controllers\Api\SteadfastOrderController;
use App\Http\Controllers\Api\StockAdjustmentController;
use App\Http\Controllers\Api\StockMovementRequestController;
use App\Http\Controllers\Api\SubCategoryController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\SuperAdminEmailSendController;
use App\Http\Controllers\Api\SuperAdminSmsSendController;
use App\Http\Controllers\Api\SupportDepartmentController;
use App\Http\Controllers\Api\SupportTicketController;
use App\Http\Controllers\Api\TaxGroupController;
use App\Http\Controllers\Api\TaxRateController;
use App\Http\Controllers\Api\TemplateController;
use App\Http\Controllers\Api\TransactionExpenseController;
use App\Http\Controllers\Api\TransactionIncomeController;
use App\Http\Controllers\Api\TransactionInternalTransferController;
use App\Http\Controllers\Api\TransactionJournalController;
use App\Http\Controllers\Api\TypePeriodController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\Production\ProductionDashboardController;
use App\Http\Controllers\Api\Production\BillOfMaterialController;
use App\Http\Controllers\Api\Production\WorkCenterController;
use App\Http\Controllers\Api\Production\ProductionStageController;
use App\Http\Controllers\Api\Production\ProductionPlanningController;
use App\Http\Controllers\Api\Production\ProductionOrderController;
use App\Http\Controllers\Api\Production\ProductionWastageController;
use App\Http\Controllers\Api\Production\ProductionQualityCheckController;
use App\Http\Controllers\Api\Production\ProductionCostController;
use App\Http\Controllers\Api\Production\ProductionReportController;
use App\Http\Controllers\Api\Production\ProductionSettingController;
use App\Http\Controllers\Api\UserPasswordController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\WarehouseInventoryController;
use App\Http\Controllers\Api\WocommerceSettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;



Route::prefix('v1')->group(function () {

    Route::get('/ping', function () {
        return 'pong';
    });
    Route::get('/test-subscribe/{channelId}', function ($channelId) {
        $channel = \App\Models\Channel::findOrFail($channelId);

        $response = \Illuminate\Support\Facades\Http::post(
            "https://graph.facebook.com/v20.0/{$channel->page_id}/subscribed_apps",
            [
                'access_token' => $channel->page_token,
                'subscribed_fields' => 'messages,messaging_postbacks',
            ]
        );

        return $response->json();
    });
    Route::get('/inbox/meta/callback', [MetaConnectController::class, 'callback']);
    Route::get('/webhook/meta', [MetaWebhookController::class, 'verify']);
    Route::post('/webhook/meta', [MetaWebhookController::class, 'handle']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::get('/customer-payment-methods/public', [CustomerPaymentMethodController::class, 'publicMethod']);
    Route::post('/auth/forgot-password/request-otp', [AuthController::class, 'requestOtp']);
    Route::post('auth/forgot-password/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::prefix('registration')->group(function () {
        Route::get('pricings', [CompanyRegistrationController::class, 'pricings']);
        Route::post('company', [CompanyRegistrationController::class, 'storeBasic']);
        Route::post('subscription', [CompanyRegistrationController::class, 'storeSubscription']);
        Route::post('basic-settings', [CompanyRegistrationController::class, 'storeBasicSettings']);


        // OTP actions
        Route::post('verify-otp', [CompanyRegistrationController::class, 'verifyOtp']);
        Route::post('resend-otp', [CompanyRegistrationController::class, 'resendOtp']);
    });

    Route::post('/wocommerces/webhook/orders/{settingId}', [\App\Http\Controllers\Api\WoocommerceWebhookController::class, 'handle']);
    Route::post('/steadfast/webhook', [\App\Http\Controllers\Api\SteadfastWebhookController::class, 'handle']);
    Route::post('/pathao/webhook', [\App\Http\Controllers\Api\PathaoWebhookController::class, 'handle']);
    Route::middleware('auth:sanctum', 'company.access')->group(function () {
        Route::post('/clear-cache', function () {
            Artisan::call('cache:clear');
            return response()->json(['message' => 'Cache cleared successfully']);
        });
        //auth
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::get('auth/login-history', [AuthController::class, 'historyLoginAll']);
        Route::get('auth/profile', [AuthController::class, 'profile']);
        Route::post('auth/profile/update', [AuthController::class, 'updateProfile']);
        Route::post('auth/password/update', [AuthController::class, 'updatePassword']);
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/logout-all', [AuthController::class, 'logoutAll']);
        Route::delete('auth/account', [AuthController::class, 'deleteAccount']);
        Route::prefix('companies')->group(function () {
            Route::get('/search/query', [CompanyController::class, 'search']);
            Route::get('/active/list', [CompanyController::class, 'getActiveCompanies']);
            Route::get('/', [CompanyController::class, 'index']);
            Route::post('/', [CompanyController::class, 'store']);
            Route::get('/{id}', [CompanyController::class, 'show']);
            Route::post('/update/{id}', [CompanyController::class, 'update'])->name('company');
            Route::post('/{id}/update-requests', [CompanyController::class, 'storeUpdateRequest']);
            Route::patch('/update-requests/{id}/approve', [CompanyController::class, 'approveUpdateRequest']);
            Route::patch('/update-requests/{id}/reject',  [CompanyController::class, 'rejectUpdateRequest']);
            Route::get('/{id}/delation-summary', [CompanyController::class, 'deletionSummary']);
            Route::get('/{id}/can-delete', [CompanyController::class, 'canDeleteCompany']);
            Route::delete('/{id}', [CompanyController::class, 'destroy']);
            Route::get('/{id}/restore', [CompanyController::class, 'restore']);
            Route::delete('/{id}/force', [CompanyController::class, 'forceDestroy']);
            Route::patch('/{id}/toggle-status', [CompanyController::class, 'toggleStatus']);
            Route::get('/{id}/profile', [CompanyController::class, 'getProfile']);
        });
        Route::middleware('check.user.status:allow_pending')->group(function () {

            Route::get('/dashboard/setup-progress', [SetupProgressController::class, 'getProgress']);

            Route::prefix('site-settings')->group(function () {
                Route::get('/', [SiteSettingController::class, 'index']);
                Route::post('/', [SiteSettingController::class, 'store']);
                Route::get('/{id}', [SiteSettingController::class, 'show']);
                Route::post('/update/{id}', [SiteSettingController::class, 'update']);
                Route::delete('/{id}', [SiteSettingController::class, 'destroy']);
                Route::get('{id}/restore', [SiteSettingController::class, 'restore']);
                Route::delete('{id}/force', [SiteSettingController::class, 'forceDestroy']);
                Route::patch('/{id}/toggle-status', [SiteSettingController::class, 'toggleStatus']);
            });
        });
        Route::get('/reports/payment-collection', [PaymentCollectionController::class, 'paymentCollection']);
        Route::get('/payment-collection/parties', [PaymentCollectionController::class, 'getDueParties']);
        Route::get('/payment-collection/parties/{partyId}/invoices', [PaymentCollectionController::class, 'getPartyDueInvoices']);
        Route::post('/payment-collection/parties/{partyId}/settle', [PaymentCollectionController::class, 'settlePayments']);
        Route::get('/payment-collection/transactions', [PaymentCollectionController::class, 'transactions']); 
        Route::get('/site-basic-data', [SiteSettingController::class, 'basicData']);
        Route::get('/company/package-usage', [PackageUsageController::class, 'index']);
        Route::post('/subscriptions/{id}/payments', [SubscriptionController::class, 'addpayment']);
        Route::post('/subscriptions/upgrade-payment', [SubscriptionController::class, 'upgradePayment']);
        Route::patch('/subscription-payments/{id}/status', [SubscriptionController::class, 'updateStatus']);
        Route::get('/pricing-packages', [PricingPackageController::class, 'index']);
        Route::patch('/companies/{id}/subscription/upgrade', [CompanyController::class, 'upgradeSubscriptionSuperAdmin']);
        Route::post('/subscriptions/upgrade/prev', [CompanyController::class, 'upgradeSubscription']);
        Route::post('/subscriptions/upgrade', [PackageUpgradeController::class, 'store']);
        Route::post('/companies/{id}/update-requests', [CompanyController::class, 'storeUpdateRequest']);
        Route::post('/companies/update-requests/{reqId}', [CompanyController::class, 'updateUpdateRequest']);
        Route::delete('/companies/update-requests/{reqId}', [CompanyController::class, 'deleteUpdateRequest']);
        Route::get('/companies/{id}/extra-charges', [CompanyController::class, 'extraCharges']);
        Route::get('/system-pages', [SystemPageController::class, 'index']);
        Route::get('/system-pages/{pageType}', [SystemPageController::class, 'show']);
        Route::put('/system-pages/{pageType}', [SystemPageController::class, 'update']);
        Route::middleware('subscription.active')->group(function () {
            Route::middleware(['super_admin'])->group(function () {
                Route::post('/impersonate/company/{company}', [AuthController::class, 'impersonateCompany']);
                Route::patch('/subscriptions/{id}/discount', [SubscriptionController::class, 'applyDiscount']);
                Route::get('/billing/companies', [SubscriptionController::class, 'billing']);
            });

            Route::get('/menu-settings', [MenuSettingController::class, 'index']);
            Route::get('/menu-settings/{menuSetting}', [MenuSettingController::class, 'show']);
            Route::post('/menu-settings', [MenuSettingController::class, 'store']);
            Route::put('/menu-settings/{menuSetting}', [MenuSettingController::class, 'update']);
            Route::patch('/menu-settings/{menuSetting}/status', [MenuSettingController::class, 'toggleStatus']);
            Route::delete('/menu-settings/{menuSetting}', [MenuSettingController::class, 'destroy']);
            //site settings routes


            Route::post('/settings/update-invoice-template', [SiteSettingController::class, 'updateInvoiceTemplate']);
            Route::post('/settings/update-theme-template', [SiteSettingController::class, 'updateThemeTemplate']);
            //user status check middleware
            Route::middleware('check.user.status')->group(function () {

                //action logs
                Route::prefix('logs')->group(function () {
                    Route::get('/', [LogActionController::class, 'index']);
                    Route::get('/stats', [LogActionController::class, 'stats']);
                    Route::get('/{module}/{companyId}', [LogActionController::class, 'logByModule']);
                    Route::get('/{actionId}', [LogActionController::class, 'logByAction']);
                });

                Route::prefix('action-logs')->group(function () {
                    Route::get('/',        [ActionLogController::class, 'index']);
                    Route::get('/filters', [ActionLogController::class, 'filters']);
                });
                // company routes

                Route::prefix('options')->group(function () {
                    Route::get('/warehouses', [SelectOptionController::class, 'warehouseOptions']);
                    Route::get('/product/warehouses', [SelectOptionController::class, 'productwarehouseOptions']);
                    Route::get('/areas/{warehouseId}', [SelectOptionController::class, 'areaOptions']);
                    Route::get('/racks/{areaId}', [SelectOptionController::class, 'rackOptions']);
                    Route::get('/cells/{rackId}', [SelectOptionController::class, 'cellOptions']);
                    Route::get('/bins/{warehouseId}', [SelectOptionController::class, 'binOptions']);
                    Route::get('/product/bins/{warehouseId}', [SelectOptionController::class, 'productbinOptions']);
                    Route::get('/suppliers', [SelectOptionController::class, 'supplierOptions']);
                    Route::get('/customers', [SelectOptionController::class, 'customersOptions']);
                    Route::get('/employees', [SelectOptionController::class, 'employeeOptions']);
                    Route::get('/departments', [SelectOptionController::class, 'departmentOptions']);
                    Route::get('/get-product-by-warehouse/{warehouseId}', [SelectOptionController::class, 'getProductByWarehouse']);
                    Route::get('/get-product-by-warehouse/adjustment/{warehouseId}', [SelectOptionController::class, 'getProductOptionsByWarehouse']);
                    Route::get('/get-product-by-warehouse/{warehouseId}/{binId}', [SelectOptionController::class, 'getProductByWarehouseAndBin']);
                    Route::get('/products', [SelectOptionController::class, 'productOptions']);
                    Route::get('/products/purchase', [SelectOptionController::class, 'purchaseProductOptions']);
                    Route::get('/purchases', [SelectOptionController::class, 'purchaseOptions']);
                    Route::get('/attribute-group', [SelectOptionController::class, 'attributeGroupOptions']);
                    Route::get('/product/attribute-group', [SelectOptionController::class, 'productattributeGroupOptions']);
                    Route::get('/product/attribute-value', [SelectOptionController::class, 'productattributeValueOptions']);
                    Route::get('/mega-categories', [SelectOptionController::class, 'megaCategoryOptions']);
                    Route::get('/product/mega-categories', [SelectOptionController::class, 'productmegaCategoryOptions']);
                    Route::get('/product/sub-categories', [SelectOptionController::class, 'productsubCategoryOptions']);
                    Route::get('/product/mini-categories', [SelectOptionController::class, 'productminiCategoryOptions']);
                    Route::get('/product/extra-categories', [SelectOptionController::class, 'productextraCategoryOptions']);
                    Route::get('/product/brands', [SelectOptionController::class, 'brandOptions']);
                    Route::get('/brands', [SelectOptionController::class, 'productbrandOptions']);
                    Route::get('/nested-categories', [SelectOptionController::class, 'nestedCategoryOptions']);
                    Route::get('/users', [SelectOptionController::class, 'userOptions']);
                    Route::get('/super-admin-users', [SelectOptionController::class, 'superAdminUserOptions']);
                    Route::get('/asset-categories', [SelectOptionController::class, 'assetCategoryOptions']);
                    Route::get('/assets', [SelectOptionController::class, 'assetOptions']);
                    Route::get('/disposal-types', [SelectOptionController::class, 'disposalTypeOptions']);
                    Route::get('/account-groups', [SelectOptionController::class, 'accountGroupOptions']);
                    Route::get('/account-expenses', [SelectOptionController::class, 'accountExpenseOptions']);
                    Route::get('/payment-accounts', [SelectOptionController::class, 'paymentAccountOptions']);
                    Route::get('/income-accounts', [SelectOptionController::class, 'incomeAccountOptions']);
                    Route::get('/account-charts', [SelectOptionController::class, 'accountChartOptions']);
                    Route::get('/support-departments', [SelectOptionController::class, 'supportDepartmentOptions']);
                    Route::get('/customer-groups', [SelectOptionController::class, 'customerGroups']);
                    Route::get('/landing-domains', [SelectOptionController::class, 'getAvailableDomains']);
                    Route::get('/roles', [SelectOptionController::class, 'getRoles']);
                    Route::get('/email-templates', [SelectOptionController::class, 'getEmailTemplate']);
                    Route::get('/sms-templates', [SelectOptionController::class, 'getSmsTemplate']);
                });
                //party routes
                Route::prefix('parties')->group(function () {

                    Route::get('search', [PartyController::class, 'search']);
                    Route::get('suppliers', [PartyController::class, 'getSuppliers']);
                    Route::get('customers', [PartyController::class, 'getCustomers']);
                    Route::get('/export-template', [PartyController::class, 'exportTemplate']);
                    Route::get('/export', [PartyController::class, 'exportData']);
                    Route::post('/import', [PartyController::class, 'import']);
                    Route::get('/', [PartyController::class, 'index']);
                    Route::post('/', [PartyController::class, 'store']);
                    Route::get('/{id}', [PartyController::class, 'show']);
                    Route::get('/{id}/supplier-profile', [PartyController::class, 'supplierProfile']);
                    Route::post('/update/{id}', [PartyController::class, 'update']);
                    Route::delete('/{id}', [PartyController::class, 'destroy']);
                    Route::get('/{id}/restore', [PartyController::class, 'restore']);
                    Route::delete('/{id}/force', [PartyController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [PartyController::class, 'toggleStatus']);
                    Route::patch('/{id}/update-balance', [PartyController::class, 'updateBalance']);
                    Route::get('/{id}/profile', [PartyController::class, 'profile']);
                });

                //attribute group
                Route::prefix('attribute-group')->group(function () {

                    Route::get('/', [AttributeGroupController::class, 'index']);
                    Route::post('/', [AttributeGroupController::class, 'store']);
                    Route::get('/{id}', [AttributeGroupController::class, 'show']);
                    Route::post('/update/{id}', [AttributeGroupController::class, 'update']);
                    Route::delete('/{id}', [AttributeGroupController::class, 'destroy']);
                    Route::get('/{id}/restore', [AttributeGroupController::class, 'restore']);
                    Route::delete('/{id}/force', [AttributeGroupController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [AttributeGroupController::class, 'toggleStatus']);
                });

                // Attribute Routes
                Route::prefix('attribute-value')->group(function () {


                    Route::get('/', [AttributeValueController::class, 'index']);
                    Route::post('/', [AttributeValueController::class, 'store']);
                    Route::get('/{id}', [AttributeValueController::class, 'show']);
                    Route::post('/update/{id}', [AttributeValueController::class, 'update']);
                    Route::delete('/{id}', [AttributeValueController::class, 'destroy']);
                    Route::get('/{id}/restore', [AttributeValueController::class, 'restore']);
                    Route::delete('/{id}/force', [AttributeValueController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [AttributeValueController::class, 'toggleStatus']);
                });

                // Brand Routes
                Route::prefix('brands')->group(function () {

                    Route::get('/', [BrandController::class, 'index']);
                    Route::post('/', [BrandController::class, 'store']);
                    Route::get('/{id}', [BrandController::class, 'show']);
                    Route::post('/update/{id}', [BrandController::class, 'update']);
                    Route::delete('/{id}', [BrandController::class, 'destroy']);
                    Route::get('/{id}/restore', [BrandController::class, 'restore']);
                    Route::delete('/{id}/force', [BrandController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [BrandController::class, 'toggleStatus']);
                });
                Route::prefix('master-brands')->group(function () {
                    Route::get('/', [MasterBrandController::class, 'index']);
                    Route::post('/', [MasterBrandController::class, 'store']);
                    Route::get('/{id}', [MasterBrandController::class, 'show']);
                    Route::post('/update/{id}', [MasterBrandController::class, 'update']);
                    Route::delete('/{id}', [MasterBrandController::class, 'destroy']);
                    Route::get('/{id}/restore', [MasterBrandController::class, 'restore']);
                    Route::delete('/{id}/force', [MasterBrandController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [MasterBrandController::class, 'toggleStatus']);
                });

                Route::prefix('features')->group(function () {
                    Route::get('/', [MasterFeatureController::class, 'index']);
                    Route::post('/', [MasterFeatureController::class, 'store']);
                    Route::get('/{id}', [MasterFeatureController::class, 'show']);
                    Route::post('/update/{id}', [MasterFeatureController::class, 'update']);
                    Route::delete('/{id}', [MasterFeatureController::class, 'destroy']);
                    Route::get('/{id}/restore', [MasterFeatureController::class, 'restore']);
                    Route::delete('/{id}/force', [MasterFeatureController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [MasterFeatureController::class, 'toggleStatus']);
                });

                Route::prefix('demos')->group(function () {
                    Route::get('/', [MasterDemoController::class, 'index']);
                    Route::post('/', [MasterDemoController::class, 'store']);
                    Route::get('/{id}', [MasterDemoController::class, 'show']);
                    Route::post('/update/{id}', [MasterDemoController::class, 'update']);
                    Route::delete('/{id}', [MasterDemoController::class, 'destroy']);
                    Route::get('/{id}/restore', [MasterDemoController::class, 'restore']);
                    Route::delete('/{id}/force', [MasterDemoController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [MasterDemoController::class, 'toggleStatus']);
                });
                // Mega Category Routes
                Route::prefix('mega-categories')->group(function () {

                    Route::get('/', [MegaCategoryController::class, 'index']);
                    Route::post('/', [MegaCategoryController::class, 'store']);
                    Route::get('/{id}', [MegaCategoryController::class, 'show']);
                    Route::post('/update/{id}', [MegaCategoryController::class, 'update']);
                    Route::delete('/{id}', [MegaCategoryController::class, 'destroy']);
                    Route::get('/{id}/restore', [MegaCategoryController::class, 'restore']);
                    Route::delete('/{id}/force', [MegaCategoryController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [MegaCategoryController::class, 'toggleStatus']);
                    Route::patch('/{id}/update-order', [MegaCategoryController::class, 'updateOrder']);
                });

                // Sub Category Routes
                Route::prefix('sub-categories')->group(function () {
                    Route::get('search', [SubCategoryController::class, 'search']);

                    Route::get('by-mega', [SubCategoryController::class, 'getByMegaCategory']);

                    Route::get('/', [SubCategoryController::class, 'index']);
                    Route::post('/', [SubCategoryController::class, 'store']);
                    Route::get('/{id}', [SubCategoryController::class, 'show']);
                    Route::post('/update/{id}', [SubCategoryController::class, 'update']);
                    Route::delete('/{id}', [SubCategoryController::class, 'destroy']);

                    Route::get('/{id}/restore', [SubCategoryController::class, 'restore']);
                    Route::delete('/{id}/force', [SubCategoryController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [SubCategoryController::class, 'toggleStatus']);
                    Route::patch('/{id}/update-order', [SubCategoryController::class, 'updateOrder']);
                });

                // Mini Category Routes
                Route::prefix('mini-categories')->group(function () {
                    Route::get('by-sub', [MiniCategoryController::class, 'getBySubCategory']);
                    Route::get('/', [MiniCategoryController::class, 'index']);
                    Route::post('/', [MiniCategoryController::class, 'store']);
                    Route::get('/{id}', [MiniCategoryController::class, 'show']);
                    Route::post('/update/{id}', [MiniCategoryController::class, 'update']);

                    Route::delete('/{id}', [MiniCategoryController::class, 'destroy']);

                    Route::get('/{id}/restore', [MiniCategoryController::class, 'restore']);
                    Route::delete('/{id}/force', [MiniCategoryController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [MiniCategoryController::class, 'toggleStatus']);
                    Route::patch('/{id}/update-order', [MiniCategoryController::class, 'updateOrder']);
                });

                // Extra Category Routes
                Route::prefix('extra-categories')->group(function () {
                    Route::get('by-mini', [ExtraCategoryController::class, 'getByMiniCategory']);

                    Route::get('/', [ExtraCategoryController::class, 'index']);
                    Route::post('/', [ExtraCategoryController::class, 'store']);
                    Route::get('/{id}', [ExtraCategoryController::class, 'show']);
                    Route::get('/{id}/hierarchy', [ExtraCategoryController::class, 'getFullHierarchy']);
                    Route::post('/update/{id}', [ExtraCategoryController::class, 'update']);
                    Route::delete('/{id}', [ExtraCategoryController::class, 'destroy']);
                    Route::get('/{id}/restore', [ExtraCategoryController::class, 'restore']);
                    Route::delete('/{id}/force', [ExtraCategoryController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [ExtraCategoryController::class, 'toggleStatus']);
                    Route::patch('/{id}/update-order', [ExtraCategoryController::class, 'updateOrder']);
                });
                // Warehouse Routes
                Route::prefix('warehouses')->group(function () {
                    Route::get('/', [WarehouseController::class, 'index']);
                    Route::post('/', [WarehouseController::class, 'store']);
                    Route::get('/{id}', [WarehouseController::class, 'show']);
                    Route::post('/update/{id}', [WarehouseController::class, 'update']);
                    Route::delete('/{id}', [WarehouseController::class, 'destroy']);
                    Route::get('/{id}/restore', [WarehouseController::class, 'restore']);
                    Route::delete('/{id}/force', [WarehouseController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [WarehouseController::class, 'toggleStatus']);
                });
                // Area Routes
                Route::prefix('areas')->group(function () {
                    Route::get('/', [AreaController::class, 'index']);
                    Route::post('/', [AreaController::class, 'store']);
                    Route::get('/{id}', [AreaController::class, 'show']);
                    Route::post('/update/{id}', [AreaController::class, 'update']);
                    Route::delete('/{id}', [AreaController::class, 'destroy']);
                    Route::get('/{id}/restore', [AreaController::class, 'restore']);
                    Route::delete('/{id}/force', [AreaController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [AreaController::class, 'toggleStatus']);
                });
                // Rack Routes
                Route::prefix('racks')->group(function () {
                    Route::get('/', [RackController::class, 'index']);
                    Route::post('/', [RackController::class, 'store']);
                    Route::get('/{id}', [RackController::class, 'show']);
                    Route::post('/update/{id}', [RackController::class, 'update']);
                    Route::delete('/{id}', [RackController::class, 'destroy']);
                    Route::get('/{id}/restore', [RackController::class, 'restore']);
                    Route::delete('/{id}/force', [RackController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [RackController::class, 'toggleStatus']);
                });
                // Cell Routes
                Route::prefix('cells')->group(function () {
                    Route::get('/', [CellController::class, 'index']);
                    Route::post('/', [CellController::class, 'store']);
                    Route::get('/{id}', [CellController::class, 'show']);
                    Route::post('/update/{id}', [CellController::class, 'update']);
                    Route::delete('/{id}', [CellController::class, 'destroy']);
                    Route::get('/{id}/restore', [CellController::class, 'restore']);
                    Route::delete('/{id}/force', [CellController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [CellController::class, 'toggleStatus']);
                });
                Route::post('clone/products/{product}', [ProductController::class, 'clone']);
                //prouducts
                Route::prefix('products')->group(function () {
                    Route::get('/', [ProductController::class, 'index']);
                    Route::post('/', [ProductController::class, 'store']);
                    Route::get('/{id}', [ProductController::class, 'show']);
                    Route::post('/update/{id}', [ProductController::class, 'update']);
                    Route::delete('/{id}', [ProductController::class, 'destroy']);
                    Route::get('/{id}/restore', [ProductController::class, 'restore']);
                    Route::delete('/{id}/force', [ProductController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [ProductController::class, 'toggleStatus']);
                    Route::patch('/{id}/update-stock', [ProductController::class, 'updateStock']);
                    Route::post('/{id}/stock/add', [ProductController::class, 'addStock']);
                    Route::post('/{id}/stock/remove', [ProductController::class, 'removeStock']);
                    Route::post('/{id}/stock/adjust', [ProductController::class, 'adjustStock']);
                    Route::get('/{id}/stock/history', [ProductController::class, 'stockHistory']);
                    Route::get('/{id}/stock/warehouse', [ProductController::class, 'warehouseStock']);
                    Route::post('/{id}/generate-barcode', [ProductController::class, 'generateBarcodes']);
                    Route::post('/bulk/generate-barcodes', [ProductController::class, 'bulkGenerateBarcodes']);
                    Route::post('/bulk/update-warehouse', [ProductController::class, 'bulkUpdateWarehouse']);
                    Route::post('/bulk/update-category', [ProductController::class, 'bulkUpdateCategory']);
                });
                Route::get('/check-sku/product', [ProductController::class, 'checkSku']);

                // Page Routes
                Route::prefix('pages')->group(function () {
                    Route::get('/', [PageController::class, 'index']);
                    Route::post('/', [PageController::class, 'store']);
                    Route::get('/{id}', [PageController::class, 'show']);
                    Route::post('/update/{id}', [PageController::class, 'update']);
                    Route::delete('/{id}', [PageController::class, 'destroy']);
                    Route::get('/{id}/restore', [PageController::class, 'restore']);
                    Route::delete('/{id}/force', [PageController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [PageController::class, 'toggleStatus']);
                });
                // Slider Routes
                Route::prefix('sliders')->group(function () {
                    Route::get('/', [SlideController::class, 'index']);
                    Route::post('/', [SlideController::class, 'store']);
                    Route::get('/{id}', [SlideController::class, 'show']);
                    Route::post('/update/{id}', [SlideController::class, 'update']);
                    Route::delete('/{id}', [SlideController::class, 'destroy']);
                    Route::get('/{id}/restore', [SlideController::class, 'restore']);
                    Route::delete('/{id}/force', [SlideController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [SlideController::class, 'toggleStatus']);
                });


                // Blog Routes
                Route::prefix('blogs')->group(function () {

                    Route::get('/', [BlogController::class, 'index']);
                    Route::post('/', [BlogController::class, 'store']);
                    Route::get('/{id}', [BlogController::class, 'show']);
                    Route::post('/update/{id}', [BlogController::class, 'update']);
                    Route::delete('/{id}', [BlogController::class, 'destroy']);
                    Route::get('/{id}/restore', [BlogController::class, 'restore']);
                    Route::delete('/{id}/force', [BlogController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [BlogController::class, 'toggleStatus']);
                });
                Route::prefix('social-settings')->group(function () {

                    Route::get('/', [SocialSettingController::class, 'index']);
                    Route::post('/', [SocialSettingController::class, 'store']);
                    Route::get('/{id}', [SocialSettingController::class, 'show']);
                    Route::post('/update/{id}', [SocialSettingController::class, 'update']);
                    Route::delete('/{id}', [SocialSettingController::class, 'destroy']);
                    Route::get('/{id}/restore', [SocialSettingController::class, 'restore']);
                    Route::delete('/{id}/force', [SocialSettingController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [SocialSettingController::class, 'toggleStatus']);
                });
                Route::post('/upload-image', [EditorImagesController::class, 'uploadImage']);
                Route::prefix('purchases')->group(function () {
                    Route::get('/', [PurchaseController::class, 'index']);
                    Route::post('/', [PurchaseController::class, 'store']);
                    Route::get('/{id}', [PurchaseController::class, 'show']);
                    Route::post('/update/{id}', [PurchaseController::class, 'update']);
                    Route::delete('/{id}', [PurchaseController::class, 'destroy']);
                    Route::patch('/{id}/change-status', [PurchaseController::class, 'changeStatus']);
                    Route::post('/{id}/add-payment', [PurchaseController::class, 'addPayment']);
                    Route::get('{id}/restore', [PurchaseController::class, 'restore']);
                    Route::delete('{id}/force', [PurchaseController::class, 'forceDestroy']);
                });
                Route::prefix('purchase-returns')->group(function () {
                    Route::get('/', [PurchaseReturnController::class, 'index']);
                    Route::post('/', [PurchaseReturnController::class, 'store']);
                    Route::get('/{id}', [PurchaseReturnController::class, 'show']);
                    Route::post('/update/{id}', [PurchaseReturnController::class, 'update']);
                    Route::delete('/{id}', [PurchaseReturnController::class, 'destroy']);
                    Route::patch('/{id}/change-status', [PurchaseReturnController::class, 'changeStatus']);
                    Route::get('{id}/restore', [PurchaseReturnController::class, 'restore']);
                    Route::delete('{id}/force', [PurchaseReturnController::class, 'forceDestroy']);
                    Route::post('/{id}/add-payment', [PurchaseReturnController::class, 'addPayment']);
                    Route::get('/products/{id}', [PurchaseReturnController::class, 'purchaseProducts']);
                });
                Route::prefix('coupons')->group(function () {
                    Route::get('/', [CouponController::class, 'index']);
                    Route::post('/', [CouponController::class, 'store']);
                    Route::get('/{id}', [CouponController::class, 'show']);
                    Route::post('/update/{id}', [CouponController::class, 'update']);
                    Route::delete('/{id}', [CouponController::class, 'destroy']);
                    Route::patch('/{id}/change-status', [CouponController::class, 'changeStatus']);
                    Route::post('/validate', [CouponController::class, 'validate']);
                });
                Route::prefix('order-note')->group(function () {
                    Route::get('/', [OrderNoteController::class, 'index']);
                    Route::post('/', [OrderNoteController::class, 'store']);
                    Route::get('/{id}', [OrderNoteController::class, 'show']);
                    Route::post('/update/{id}', [OrderNoteController::class, 'update']);
                    Route::delete('/{id}', [OrderNoteController::class, 'destroy']);
                });
                Route::prefix('pos-order')->group(function () {
                    Route::get('/', [PosOrderController::class, 'index']);
                    Route::post('/', [PosOrderController::class, 'store']);
                    Route::get('/{id}', [PosOrderController::class, 'show']);
                    Route::get('hold/list/{warehouseId}', [PosOrderController::class, 'heldOrders']);
                    Route::post('{id}/cancel', [PosOrderController::class, 'cancel']);
                    Route::post('{id}/complete', [PosOrderController::class, 'complete']);
                    Route::post('{id}/hold', [PosOrderController::class, 'hold']);
                    Route::post('{id}/resume', [PosOrderController::class, 'resume']);
                    Route::get('/pos/dashboard', [PosOrderController::class, 'dashboard']);
                });
                Route::prefix('sales-order')->group(function () {
                    Route::get('/', [SalesOrderController::class, 'index']);
                    Route::post('/', [SalesOrderController::class, 'store']);
                    Route::get('/{id}', [SalesOrderController::class, 'show']);
                    Route::post('{id}/cancel', [SalesOrderController::class, 'cancel']);
                    Route::post('{id}/complete', [SalesOrderController::class, 'complete']);
                    Route::post('/{id}/assign-users', [SalesOrderController::class, 'assignUsers']);
                });
                Route::post('/parties/{id}/pay-order-from-wallet', [OrderController::class, 'payOrderFromWallet']);

                Route::prefix('fetch-orders')->group(function () {
                    Route::get('/select/order-list-option', [OrderController::class, 'getSelectListOrder']);
                    Route::get('/', [OrderController::class, 'index']);
                    Route::get('/{id}', [OrderController::class, 'show']);
                    Route::put('/{id}/status', [OrderController::class, 'updateStatus']);
                    Route::patch('/{id}/change-status', [OrderController::class, 'changeStatus']);
                    Route::delete('/{id}', [OrderController::class, 'deleteOrder']);
                    Route::put('/{id}/update', [OrderController::class, 'update']);
                    Route::put('/{id}/update-shipping', [OrderController::class, 'updateShiping']);
                    Route::post('/{id}/add-payment', [OrderController::class, 'addPayment']);
                    Route::get('/cutomer/{customerId}', [OrderController::class, 'customerOrders']);
                    Route::get('/edit-order/{id}', [OrderController::class, 'getEditOrder']);
                    Route::get('/products/{id}', [OrderController::class, 'orderProducts']);
                });
                Route::post('/orders/bulk-status-update', [OrderController::class, 'bulkStatusUpdate']);
                Route::get('/courier-check', [OrderController::class, 'checkCourier']);

                Route::prefix('orders-return')->group(function () {
                    Route::get('/', [OrderReturnController::class, 'index']);
                    Route::post('/', [OrderReturnController::class, 'store']);
                    Route::get('/{id}', [OrderReturnController::class, 'show']);
                    Route::post('/update/{id}', [OrderReturnController::class, 'update']);
                    Route::delete('/{id}', [OrderReturnController::class, 'destroy']);
                    Route::patch('/{id}/change-status', [OrderReturnController::class, 'changeStatus']);
                    Route::post('/{id}/add-payment', [OrderReturnController::class, 'addPayment']);
                    Route::post('/{id}/modify-refund', [OrderReturnController::class, 'modifyRefund']);

                    Route::get('{id}/restore', [OrderReturnController::class, 'restore']);
                    Route::delete('{id}/force', [OrderReturnController::class, 'forceDestroy']);
                });

                Route::prefix('quotations')->group(function () {
                    Route::get('/', [QuotationController::class, 'index']);
                    Route::post('/', [QuotationController::class, 'store']);
                    Route::get('/{id}', [QuotationController::class, 'show']);
                    Route::put('/{id}', [QuotationController::class, 'update']);
                    Route::delete('/{id}', [QuotationController::class, 'destroy']);

                    // Status and conversion
                    Route::patch('/{id}/change-status', [QuotationController::class, 'changeStatus']);
                    Route::post('/{id}/convert-to-order', [QuotationController::class, 'convertToOrder']);

                    // Soft delete management
                    Route::get('/{id}/restore', [QuotationController::class, 'restore']);
                    Route::delete('/{id}/force', [QuotationController::class, 'forceDestroy']);

                    // status update
                    Route::post('/bulk-status-update', [QuotationController::class, 'bulkStatus']);
                    Route::post('/bulk-delete', [QuotationController::class, 'bulkDestroy']);
                });

                Route::prefix('bin')->group(function () {
                    Route::get('/', [BinController::class, 'index']);
                    Route::post('/', [BinController::class, 'store']);
                    Route::get('/{id}', [BinController::class, 'show']);
                    Route::put('/{id}', [BinController::class, 'update']);
                    Route::delete('/{id}', [BinController::class, 'destroy']);

                    // Status change
                    Route::post('/{id}/change-status', [BinController::class, 'changeStatus']);

                    // Filter by location
                    Route::get('/by-warehouse', [BinController::class, 'byWarehouse']);
                    Route::get('/by-area', [BinController::class, 'byArea']);
                    Route::get('/by-rack', [BinController::class, 'byRack']);
                });
                Route::prefix('inventory')->group(function () {
                    Route::get('/summary', [InventoryController::class, 'index']);
                    Route::get('movements', [InventoryController::class, 'movements']);
                    Route::get('/warehouse-dashboard', [WarehouseInventoryController::class, 'dashboard']);
                    Route::get('/warehouses/{id}/stats', [WarehouseInventoryController::class, 'stats']);
                    Route::get('/warehouses/{id}/top-products', [WarehouseInventoryController::class, 'topProducts']);
                    Route::get('/warehouses/{id}/recent-movements', [WarehouseInventoryController::class, 'recentMovements']);
                    // CRUD operations
                    Route::post('movements', [InventoryController::class, 'store']);
                    Route::get('movements/{id}', [InventoryController::class, 'show']);
                    Route::put('movements/{id}', [InventoryController::class, 'update']);
                    Route::delete('movements/{id}', [InventoryController::class, 'destroy']);

                    // Actions
                    Route::post('movements/{id}/approve', [InventoryController::class, 'approve']);
                    Route::post('movements/{id}/cancel', [InventoryController::class, 'cancel']);
                });
                Route::prefix('inventroy/movement-requests')->group(function () {
                    Route::get('/', [StockMovementRequestController::class, 'index']);
                    Route::post('/', [StockMovementRequestController::class, 'store']);
                    Route::get('/{id}', [StockMovementRequestController::class, 'show']);
                    Route::put('/{id}', [StockMovementRequestController::class, 'update']);
                    Route::delete('/{id}', [StockMovementRequestController::class, 'destroy']);

                    // Actions
                    Route::post('/{id}/approve', [StockMovementRequestController::class, 'approve']);
                    Route::post('/{id}/reject', [StockMovementRequestController::class, 'reject']);
                    Route::post('/{id}/cancel', [StockMovementRequestController::class, 'cancel']);
                    Route::post('/{id}/convert-to-movement', [StockMovementRequestController::class, 'convertToMovement']);
                });
                Route::prefix('inventroy/adjustments')->group(function () {
                    Route::get('/', [StockAdjustmentController::class, 'index']);
                    Route::post('/', [StockAdjustmentController::class, 'store']);
                    Route::get('/{id}', [StockAdjustmentController::class, 'show']);
                    Route::put('/{id}', [StockAdjustmentController::class, 'update']);
                    Route::delete('/{id}', [StockAdjustmentController::class, 'destroy']);
                    Route::post('/{id}/restore', [StockAdjustmentController::class, 'restore']);
                    Route::delete('/{id}/force', [StockAdjustmentController::class, 'forceDestroy']);
                });
                // Inventory Audit Routes
                Route::prefix('inventory-audits')->group(function () {
                    // List and Create
                    Route::get('/', [InventoryAuditController::class, 'index']);
                    Route::post('/', [InventoryAuditController::class, 'store']);

                    // View, Update, Delete
                    Route::get('/{id}', [InventoryAuditController::class, 'show']);
                    Route::put('/{id}', [InventoryAuditController::class, 'update']);
                    Route::delete('/{id}', [InventoryAuditController::class, 'destroy']);

                    // Audit Actions
                    Route::post('/{id}/start', [InventoryAuditController::class, 'start']);
                    Route::post('/{id}/complete', [InventoryAuditController::class, 'complete']);
                    Route::post('/{id}/cancel', [InventoryAuditController::class, 'cancel']);

                    // Update Item Count
                    Route::patch('/{auditId}/items/{itemId}/count', [InventoryAuditController::class, 'updateItemCount']);
                });
                Route::prefix('requisitions')->group(function () {
                    Route::get('/', [RequisitionController::class, 'index']);
                    Route::post('/', [RequisitionController::class, 'store']);
                    Route::get('/{id}', [RequisitionController::class, 'show']);
                    Route::post('/update/{id}', [RequisitionController::class, 'update']);
                    Route::delete('/{id}', [RequisitionController::class, 'destroy']);
                    Route::patch('/{id}/change-status', [RequisitionController::class, 'changeStatus']);
                    Route::post('{id}/approve', [RequisitionController::class, 'approve']);
                    Route::post('{id}/reject', [RequisitionController::class, 'reject']);
                    Route::post('{id}/complete', [RequisitionController::class, 'complete']);
                    Route::get('{id}/restore', [RequisitionController::class, 'restore']);
                    Route::delete('{id}/force', [RequisitionController::class, 'forceDestroy']);
                    Route::get('/{id}/convert-data', [RequisitionController::class, 'convertData']);
                });


                // Landing Pages Routes
                Route::prefix('landing-pages')->group(function () {
                    Route::get('/', [LandingPageController::class, 'index']);
                    Route::post('/', [LandingPageController::class, 'store']);
                    Route::get('/{id}', [LandingPageController::class, 'show']);
                    Route::get('/slug/{slug}', [LandingPageController::class, 'showBySlug']);
                    Route::post('/update/{id}', [LandingPageController::class, 'update']);
                    Route::patch('/{id}', [LandingPageController::class, 'update']);
                    Route::delete('/{id}', [LandingPageController::class, 'destroy']);
                    Route::get('/{id}/restore', [LandingPageController::class, 'restore']);
                    Route::delete('/{id}/force', [LandingPageController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [LandingPageController::class, 'toggleStatus']);
                });
                Route::get('/landing/products/{id}', [LandingPageController::class, 'landingProducts']);
                // job title Routes
                Route::prefix('jobs')->group(function () {
                    Route::get('/', [JobTitleController::class, 'index']);
                    Route::post('/', [JobTitleController::class, 'store']);
                    Route::get('/{id}', [JobTitleController::class, 'show']);
                    Route::post('/update/{id}', [JobTitleController::class, 'update']);
                    Route::delete('/{id}', [JobTitleController::class, 'destroy']);
                    Route::get('/{id}/restore', [JobTitleController::class, 'restore']);
                    Route::delete('/{id}/force', [JobTitleController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [JobTitleController::class, 'toggleStatus']);
                });
                Route::get('/hrm-dashboard', [\App\Http\Controllers\Api\HrmDashboardController::class, 'index']);

                // department Routes
                Route::prefix('departments')->group(function () {
                    Route::get('/', [DepartmentController::class, 'index']);
                    Route::post('/', [DepartmentController::class, 'store']);
                    Route::get('/{id}', [DepartmentController::class, 'show']);
                    Route::post('/update/{id}', [DepartmentController::class, 'update']);
                    Route::delete('/{id}', [DepartmentController::class, 'destroy']);
                    Route::get('/{id}/restore', [DepartmentController::class, 'restore']);
                    Route::delete('/{id}/force', [DepartmentController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [DepartmentController::class, 'toggleStatus']);
                });
                // employee type Routes
                Route::prefix('employee-types')->group(function () {
                    Route::get('/', [EmployeeTypeController::class, 'index']);
                    Route::post('/', [EmployeeTypeController::class, 'store']);
                    Route::get('/{id}', [EmployeeTypeController::class, 'show']);
                    Route::post('/update/{id}', [EmployeeTypeController::class, 'update']);
                    Route::delete('/{id}', [EmployeeTypeController::class, 'destroy']);
                    Route::get('/{id}/restore', [EmployeeTypeController::class, 'restore']);
                    Route::delete('/{id}/force', [EmployeeTypeController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [EmployeeTypeController::class, 'toggleStatus']);
                });
                // office_locations type Routes
                Route::prefix('office-locations')->group(function () {
                    Route::get('/', [OfficeLocationController::class, 'index']);
                    Route::post('/', [OfficeLocationController::class, 'store']);
                    Route::get('/{id}', [OfficeLocationController::class, 'show']);
                    Route::post('/update/{id}', [OfficeLocationController::class, 'update']);
                    Route::delete('/{id}', [OfficeLocationController::class, 'destroy']);
                    Route::get('/{id}/restore', [OfficeLocationController::class, 'restore']);
                    Route::delete('/{id}/force', [OfficeLocationController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [OfficeLocationController::class, 'toggleStatus']);
                });
                // employee Routes
                Route::prefix('employees')->group(function () {
                    Route::get('/', [EmployeeController::class, 'index']);
                    Route::post('/', [EmployeeController::class, 'store']);
                    Route::get('/{id}', [EmployeeController::class, 'show']);
                    Route::post('/update/{id}', [EmployeeController::class, 'update']);
                    Route::delete('/{id}', [EmployeeController::class, 'destroy']);
                    Route::get('/{id}/restore', [EmployeeController::class, 'restore']);
                    Route::delete('/{id}/force', [EmployeeController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [EmployeeController::class, 'toggleStatus']);
                });
                //attendance routes
                Route::prefix('attendances')->group(function () {
                    Route::post('/bulk', [AttendanceController::class, 'bulkStore']);
                    Route::get('/', [AttendanceController::class, 'index']);
                    Route::post('/', [AttendanceController::class, 'store']);
                    Route::get('/{id}', [AttendanceController::class, 'show']);
                    Route::post('/update/{id}', [AttendanceController::class, 'update']);
                    Route::delete('/{id}', [AttendanceController::class, 'destroy']);
                    Route::patch('/{id}/change-status', [AttendanceController::class, 'changeStatus']);
                    Route::get('{id}/restore', [AttendanceController::class, 'restore']);
                    Route::delete('{id}/force', [AttendanceController::class, 'forceDestroy']);
                });
                //pay-heads routes
                Route::prefix('pay-heads')->group(function () {
                    Route::get('/', [PayHeadController::class, 'index']);
                    Route::post('/', [PayHeadController::class, 'store']);
                    Route::get('/{id}', [PayHeadController::class, 'show']);
                    Route::post('/update/{id}', [PayHeadController::class, 'update']);
                    Route::delete('/{id}', [PayHeadController::class, 'destroy']);
                    Route::get('{id}/restore', [PayHeadController::class, 'restore']);
                    Route::delete('{id}/force', [PayHeadController::class, 'forceDestroy']);
                });
                //period-types routes
                Route::prefix('period-types')->group(function () {
                    Route::get('/', [TypePeriodController::class, 'index']);
                    Route::post('/', [TypePeriodController::class, 'store']);
                    Route::get('/{id}', [TypePeriodController::class, 'show']);
                    Route::post('/update/{id}', [TypePeriodController::class, 'update']);
                    Route::delete('/{id}', [TypePeriodController::class, 'destroy']);
                    Route::get('{id}/restore', [TypePeriodController::class, 'restore']);
                    Route::delete('{id}/force', [TypePeriodController::class, 'forceDestroy']);
                });
                //period routes
                Route::prefix('periods')->group(function () {
                    Route::get('/', [PeriodController::class, 'index']);
                    Route::post('/', [PeriodController::class, 'store']);
                    Route::get('/{id}', [PeriodController::class, 'show']);
                    Route::post('/update/{id}', [PeriodController::class, 'update']);
                    Route::delete('/{id}', [PeriodController::class, 'destroy']);
                    Route::get('{id}/restore', [PeriodController::class, 'restore']);
                    Route::delete('{id}/force', [PeriodController::class, 'forceDestroy']);
                });
                //pay-roll routes
                Route::prefix('pay-rolls')->group(function () {
                    Route::get('/', [PayRollController::class, 'index']);
                    Route::post('/', [PayRollController::class, 'store']);
                    Route::get('/{id}', [PayRollController::class, 'show']);
                    Route::post('/update/{id}', [PayRollController::class, 'update']);
                    Route::delete('/{id}', [PayRollController::class, 'destroy']);
                    Route::get('{id}/restore', [PayRollController::class, 'restore']);
                    Route::delete('{id}/force', [PayRollController::class, 'forceDestroy']);
                    Route::post('/{id}/assign-periods', [PayRollController::class, 'assignPeriods']);
                });
                //pay-roll-pay-heads routes
                Route::prefix('pay-roll-pay-heads')->group(function () {
                    Route::get('/', [PayRollPayHeadController::class, 'index']);
                    Route::post('/', [PayRollPayHeadController::class, 'store']);
                    Route::get('/{id}', [PayRollPayHeadController::class, 'show']);
                    Route::post('/update/{id}', [PayRollPayHeadController::class, 'update']);
                    Route::delete('/{id}', [PayRollPayHeadController::class, 'destroy']);
                    Route::get('{id}/restore', [PayRollPayHeadController::class, 'restore']);
                    Route::delete('{id}/force', [PayRollPayHeadController::class, 'forceDestroy']);
                });
                //positions routes
                Route::prefix('positions')->group(function () {
                    Route::get('/', [PositionController::class, 'index']);
                    Route::post('/', [PositionController::class, 'store']);
                    Route::get('/{id}', [PositionController::class, 'show']);
                    Route::post('/update/{id}', [PositionController::class, 'update']);
                    Route::delete('/{id}', [PositionController::class, 'destroy']);
                    Route::get('{id}/restore', [PositionController::class, 'restore']);
                    Route::delete('{id}/force', [PositionController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [PositionController::class, 'toggleStatus']);
                });
                //pay slip manager routes
                Route::prefix('pay-slip-managers')->group(function () {
                    Route::get('/salary-sheet', [PaySlipManagerController::class, 'salarySheet']);
                    Route::get('/', [PaySlipManagerController::class, 'index']);
                    Route::post('/', [PaySlipManagerController::class, 'store']);
                    Route::get('/{id}', [PaySlipManagerController::class, 'show']);
                    Route::post('/regenerate', [PaySlipManagerController::class, 'regenerate']);
                });
                //resign rules routes
                Route::prefix('resign-rules')->group(function () {
                    Route::get('/', [ResignRuleController::class, 'index']);
                    Route::post('/', [ResignRuleController::class, 'store']);
                    Route::get('/{id}', [ResignRuleController::class, 'show']);
                    Route::post('/update/{id}', [ResignRuleController::class, 'update']);
                    Route::delete('/{id}', [ResignRuleController::class, 'destroy']);
                    Route::get('{id}/restore', [ResignRuleController::class, 'restore']);
                    Route::delete('{id}/force', [ResignRuleController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [ResignRuleController::class, 'toggleStatus']);
                });
                //resignations routes
                Route::prefix('resignations')->group(function () {
                    Route::get('/', [ResignationController::class, 'index']);
                    Route::post('/', [ResignationController::class, 'store']);
                    Route::get('/{id}', [ResignationController::class, 'show']);
                    Route::post('/update/{id}', [ResignationController::class, 'update']);
                    Route::patch('/{id}/toggle-status', [ResignationController::class, 'toggleStatus']);
                });
                //rejoins routes
                Route::prefix('rejoins')->group(function () {
                    Route::get('/', [RejoinController::class, 'index']);
                    Route::post('/', [RejoinController::class, 'store']);
                    Route::get('/{id}', [RejoinController::class, 'show']);
                    Route::post('/update/{id}', [RejoinController::class, 'update']);
                });
                //holidays routes
                Route::prefix('holidays')->group(function () {
                    Route::get('/', [HolidayController::class, 'index']);
                    Route::post('/', [HolidayController::class, 'store']);
                    Route::get('/{id}', [HolidayController::class, 'show']);
                    Route::post('/update/{id}', [HolidayController::class, 'update']);
                    Route::delete('/{id}', [HolidayController::class, 'destroy']);
                    Route::get('{id}/restore', [HolidayController::class, 'restore']);
                    Route::delete('{id}/force', [HolidayController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [HolidayController::class, 'toggleStatus']);
                });
                //leave-types routes
                Route::prefix('leave-types')->group(function () {
                    Route::get('/', [LeaveTypeController::class, 'index']);
                    Route::post('/', [LeaveTypeController::class, 'store']);
                    Route::get('/{id}', [LeaveTypeController::class, 'show']);
                    Route::post('/update/{id}', [LeaveTypeController::class, 'update']);
                    Route::delete('/{id}', [LeaveTypeController::class, 'destroy']);
                    Route::get('{id}/restore', [LeaveTypeController::class, 'restore']);
                    Route::delete('{id}/force', [LeaveTypeController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [LeaveTypeController::class, 'toggleStatus']);
                });
                //assign-leaves routes
                Route::prefix('assign-leaves')->group(function () {
                    Route::get('/', [AssignLeaveTypeController::class, 'index']);
                    Route::post('/', [AssignLeaveTypeController::class, 'store']);
                    Route::get('/{id}', [AssignLeaveTypeController::class, 'show']);
                    Route::post('/update/{id}', [AssignLeaveTypeController::class, 'update']);
                    Route::delete('/{id}', [AssignLeaveTypeController::class, 'destroy']);
                    Route::get('{id}/restore', [AssignLeaveTypeController::class, 'restore']);
                    Route::delete('{id}/force', [AssignLeaveTypeController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [AssignLeaveTypeController::class, 'toggleStatus']);
                });

                //leave-application routes
                Route::prefix('leave-applications')->group(function () {
                    Route::get('/', [LeaveApplicationController::class, 'index']);

                    Route::post('/', [LeaveApplicationController::class, 'store']);
                    Route::get('/{id}', [LeaveApplicationController::class, 'show']);
                    Route::post('/update/{id}', [LeaveApplicationController::class, 'update']);
                    Route::delete('/{id}', [LeaveApplicationController::class, 'destroy']);
                    Route::get('{id}/restore', [LeaveApplicationController::class, 'restore']);
                    Route::delete('{id}/force', [LeaveApplicationController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [LeaveApplicationController::class, 'toggleStatus']);
                });
                Route::prefix('bonuses')->group(function () {
                    Route::get('/', [BonusController::class, 'index']);
                    Route::post('/', [BonusController::class, 'store']);
                    Route::get('/{id}', [BonusController::class, 'show']);
                    Route::post('/update/{id}', [BonusController::class, 'update']);
                    Route::delete('/{id}', [BonusController::class, 'destroy']);
                    Route::patch('/{id}/toggle-status', [BonusController::class, 'toggleStatus']);
                });
                // Payroll Settings Route
                Route::prefix('payroll-settings')->group(function () {
                    Route::get('/', [PayrollSettingController::class, 'index']);
                    Route::post('/', [PayrollSettingController::class, 'store']); // Acts as Add & Update
                });

                // Employee Salaries Route
                Route::prefix('employee-salaries')->group(function () {
                    Route::get('/', [EmployeeSalaryController::class, 'index']);
                    Route::get('/{employeeId}', [EmployeeSalaryController::class, 'show']);
                    Route::post('/{employeeId}', [EmployeeSalaryController::class, 'store']);
                });
                Route::get('/leave-balances', [LeaveApplicationController::class, 'leaveBalances']);

                Route::prefix('payslips')->group(function () {
                    Route::post('/generate', [PayslipController::class, 'generate']);
                    Route::post('/preview', [PayslipController::class, 'previewSummary']);
                    Route::get('/', [PayslipController::class, 'index']);
                    Route::get('/{id}', [PayslipController::class, 'show']);
                });

                //wocommerce settings routes
                Route::prefix('wocommerces')->group(function () {
                    Route::get('/', [WocommerceSettingController::class, 'index']);
                    Route::post('/', [WocommerceSettingController::class, 'store']);
                    Route::get('/{id}', [WocommerceSettingController::class, 'show']);
                    Route::post('/update/{id}', [WocommerceSettingController::class, 'update']);
                    Route::delete('/{id}', [WocommerceSettingController::class, 'destroy']);
                    Route::get('{id}/restore', [WocommerceSettingController::class, 'restore']);
                    Route::delete('{id}/force', [WocommerceSettingController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [WocommerceSettingController::class, 'toggleStatus']);
                    Route::Put('/{id}/toggle-product-sync', [WocommerceSettingController::class, 'toggleProductSync']);
                    Route::Put('/{id}/toggle-order-sync', [WocommerceSettingController::class, 'toggleOrderSync']);
                    Route::post('/{id}/sync-old-orders', [WocommerceSettingController::class, 'syncOldOrders']);
                    Route::get('/import/{id}', [WocommerceSettingController::class, 'import']);
                    Route::post('/import-product', [WocommerceSettingController::class, 'importProduct']);
                });
                //status mappings routes
                Route::prefix('status-mappings')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Api\StatusMappingController::class, 'index']);
                    Route::put('/', [\App\Http\Controllers\Api\StatusMappingController::class, 'update']);
                });
                //note template settings routes
                Route::prefix('note-templates')->group(function () {
                    Route::get('/', [NoteTemplateController::class, 'index']);
                    Route::post('/', [NoteTemplateController::class, 'store']);
                    Route::get('/{id}', [NoteTemplateController::class, 'show']);
                    Route::post('/update/{id}', [NoteTemplateController::class, 'update']);
                    Route::delete('/{id}', [NoteTemplateController::class, 'destroy']);
                    Route::get('{id}/restore', [NoteTemplateController::class, 'restore']);
                    Route::delete('{id}/force', [NoteTemplateController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [NoteTemplateController::class, 'toggleStatus']);
                });
                //payment methods routes
                Route::prefix('payment-methods')->group(function () {
                    Route::get('/', [PaymentMethodTypeController::class, 'index']);
                    Route::post('/', [PaymentMethodTypeController::class, 'store']);
                    Route::get('/{id}', [PaymentMethodTypeController::class, 'show']);
                    Route::post('/update/{id}', [PaymentMethodTypeController::class, 'update']);
                    Route::delete('/{id}', [PaymentMethodTypeController::class, 'destroy']);
                    Route::get('{id}/restore', [PaymentMethodTypeController::class, 'restore']);
                    Route::delete('{id}/force', [PaymentMethodTypeController::class, 'forceDestroy']);
                });
                //customer-payments routes
                Route::prefix('customer-payments')->group(function () {

                    Route::get('/', [CustomerPaymentMethodController::class, 'index']);
                    Route::post('/', [CustomerPaymentMethodController::class, 'store']);
                    Route::get('/{id}', [CustomerPaymentMethodController::class, 'show']);
                    Route::post('/update/{id}', [CustomerPaymentMethodController::class, 'update']);
                    Route::delete('/{id}', [CustomerPaymentMethodController::class, 'destroy']);
                    Route::get('{id}/restore', [CustomerPaymentMethodController::class, 'restore']);
                    Route::delete('{id}/force', [CustomerPaymentMethodController::class, 'forceDestroy']);
                    Route::patch('{id}/toggle-status', [CustomerPaymentMethodController::class, 'toggleStatus']);
                });

                //courier-methods routes
                Route::prefix('courier-methods')->group(function () {
                    Route::get('/', [CourierMethodController::class, 'index']);
                    Route::post('/', [CourierMethodController::class, 'store']);
                    Route::get('/{id}', [CourierMethodController::class, 'show']);
                    Route::post('/update/{id}', [CourierMethodController::class, 'update']);
                    Route::delete('/{id}', [CourierMethodController::class, 'destroy']);
                    Route::get('{id}/restore', [CourierMethodController::class, 'restore']);
                    Route::delete('{id}/force', [CourierMethodController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [CourierMethodController::class, 'toggleStatus']);
                });
                //couriers routes
                Route::prefix('couriers')->group(function () {
                    Route::get('/', [CourierController::class, 'index']);
                    Route::post('/', [CourierController::class, 'store']);
                    Route::get('/{id}', [CourierController::class, 'show']);
                    Route::post('/update/{id}', [CourierController::class, 'update']);
                    Route::delete('/{id}', [CourierController::class, 'destroy']);
                    Route::get('{id}/restore', [CourierController::class, 'restore']);
                    Route::delete('{id}/force', [CourierController::class, 'forceDestroy']);
                    Route::put('/{id}/toggle-status', [CourierController::class, 'toggleStatus']);
                });

                //ip-directories routes
                Route::prefix('ip-directories')->group(function () {
                    Route::get('/', [IpDirectoryController::class, 'index']);
                    Route::post('/', [IpDirectoryController::class, 'store']);
                    Route::get('/{id}', [IpDirectoryController::class, 'show']);
                    Route::post('/update/{id}', [IpDirectoryController::class, 'update']);
                    Route::delete('/{id}', [IpDirectoryController::class, 'destroy']);
                    Route::get('{id}/restore', [IpDirectoryController::class, 'restore']);
                    Route::delete('{id}/force', [IpDirectoryController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [IpDirectoryController::class, 'toggleStatus']);
                });
                //ip-settings routes
                Route::prefix('ip-settings')->group(function () {
                    Route::get('/', [IpSettingController::class, 'index']);
                    Route::post('/', [IpSettingController::class, 'store']);
                    Route::get('/{id}', [IpSettingController::class, 'show']);
                    Route::post('/update/{id}', [IpSettingController::class, 'update']);
                    Route::delete('/{id}', [IpSettingController::class, 'destroy']);
                    Route::get('{id}/restore', [IpSettingController::class, 'restore']);
                    Route::delete('{id}/force', [IpSettingController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [IpSettingController::class, 'toggleStatus']);
                });
                Route::prefix('notifications')->group(function () {
                    Route::get('/', [NotificationController::class, 'index']);
                    Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
                    Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
                    Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
                });
                //support-departments routes
                Route::prefix('support-departments')->group(function () {
                    Route::get('/', [SupportDepartmentController::class, 'index']);
                    Route::get('/{id}', [SupportDepartmentController::class, 'show']);
                    Route::patch('{id}/response-status', [SupportTicketController::class, 'updateResponseStatus']);

                    Route::middleware(['super_admin'])->group(function () {
                        Route::post('/', [SupportDepartmentController::class, 'store']);
                        Route::post('/update/{id}', [SupportDepartmentController::class, 'update']);
                        Route::delete('/{id}', [SupportDepartmentController::class, 'destroy']);
                        Route::get('{id}/restore', [SupportDepartmentController::class, 'restore']);
                        Route::delete('{id}/force', [SupportDepartmentController::class, 'forceDestroy']);
                        Route::patch('/{id}/toggle-status', [SupportDepartmentController::class, 'toggleStatus']);
                    });
                });
                //support-tickets routes
                Route::prefix('support-tickets')->group(function () {
                    Route::get('/', [SupportTicketController::class, 'index']);
                    Route::post('/', [SupportTicketController::class, 'store']);
                    Route::get('/{id}', [SupportTicketController::class, 'show']);
                    Route::post('/{id}/assign-user', [SupportTicketController::class, 'assignUser']);
                    Route::post('/reply', [SupportTicketController::class, 'storeReply']);


                    Route::middleware(['super_admin'])->group(function () {
                        Route::post('/update/{id}', [SupportTicketController::class, 'update']);
                        Route::delete('/{id}', [SupportTicketController::class, 'destroy']);
                        Route::get('{id}/restore', [SupportTicketController::class, 'restore']);
                        Route::delete('{id}/force', [SupportTicketController::class, 'forceDestroy']);
                        Route::patch('/{id}/toggle-status', [SupportTicketController::class, 'toggleStatus']);
                    });
                });

                // Knowledge Base Routes
                Route::prefix('knowledge-bases')->group(function () {
                    Route::get('/', [KnowledgeBaseController::class, 'index']);
                    Route::post('/', [KnowledgeBaseController::class, 'store']);
                    Route::get('/{id}', [KnowledgeBaseController::class, 'show']);
                    Route::post('/update/{id}', [KnowledgeBaseController::class, 'update']);
                    Route::delete('/{id}', [KnowledgeBaseController::class, 'destroy']);
                    Route::get('/{id}/restore', [KnowledgeBaseController::class, 'restore']);
                    Route::delete('/{id}/force', [KnowledgeBaseController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [KnowledgeBaseController::class, 'toggleStatus']);
                });
                //change-pass routes
                Route::prefix('change-pass')->group(function () {
                    Route::post('/', [PassChangeController::class, 'update']);
                });
                //market tools route
                Route::get('/market-tools', [MarketController::class, 'index']);
                Route::post('/market-tools/update', [MarketController::class, 'update']);
                Route::post('/market-tools/test-capi', [MarketController::class, 'testCapi']);


                //lead-sources routes
                Route::prefix('lead-sources')->group(function () {
                    Route::get('/', [LeadSourceController::class, 'index']);
                    Route::post('/', [LeadSourceController::class, 'store']);
                    Route::get('/{id}', [LeadSourceController::class, 'show']);
                    Route::post('/update/{id}', [LeadSourceController::class, 'update']);
                    Route::delete('/{id}', [LeadSourceController::class, 'destroy']);
                    Route::get('{id}/restore', [LeadSourceController::class, 'restore']);
                    Route::delete('{id}/force', [LeadSourceController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [LeadSourceController::class, 'toggleStatus']);
                });
                //lead-status routes
                Route::prefix('lead-status')->group(function () {
                    Route::get('/', [LeadStatusController::class, 'index']);
                    Route::post('/', [LeadStatusController::class, 'store']);
                    Route::get('/{id}', [LeadStatusController::class, 'show']);
                    Route::post('/update/{id}', [LeadStatusController::class, 'update']);
                    Route::delete('/{id}', [LeadStatusController::class, 'destroy']);
                    Route::get('{id}/restore', [LeadStatusController::class, 'restore']);
                    Route::delete('{id}/force', [LeadStatusController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [LeadStatusController::class, 'toggleStatus']);
                });
                //leads routes
                Route::prefix('leads')->group(function () {
                    Route::get('/', [LeadController::class, 'index']);
                    Route::post('/', [LeadController::class, 'store']);
                    Route::get('/{id}', [LeadController::class, 'show']);
                    Route::post('/update/{id}', [LeadController::class, 'update']);
                    Route::post('/{id}/convert-to-seller', [LeadController::class, 'convertToSeller']);
                    Route::post('/{id}/convert-to-customer', [LeadController::class, 'convertToCustomer']);
                    Route::delete('/{id}', [LeadController::class, 'destroy']);
                    Route::get('{id}/restore', [LeadController::class, 'restore']);
                    Route::delete('{id}/force', [LeadController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [LeadController::class, 'toggleStatus']);
                });
                //lead-notes routes
                Route::prefix('lead-notes')->group(function () {
                    Route::get('/', [LeadNoteController::class, 'index']);
                    Route::post('/', [LeadNoteController::class, 'store']);
                    Route::get('/{id}', [LeadNoteController::class, 'show']);
                    Route::post('/update/{id}', [LeadNoteController::class, 'update']);
                    Route::delete('/{id}', [LeadNoteController::class, 'destroy']);
                    Route::get('{id}/restore', [LeadNoteController::class, 'restore']);
                    Route::delete('{id}/force', [LeadNoteController::class, 'forceDestroy']);
                });
                Route::prefix('footer-code')->group(function () {
                    Route::get('/', [FooterCodeController::class, 'index']);
                    Route::post('/', [FooterCodeController::class, 'store']);
                });
                // Domain Setup Routes
                Route::prefix('domain-setup')->group(function () {
                    Route::get('/', [DomainSetupController::class, 'index']);
                    Route::post('/', [DomainSetupController::class, 'store']);
                });
                Route::get('/domains', [DomainSetupController::class, 'domains']);
                Route::post('/domains', [DomainSetupController::class, 'multiDomain']);
                Route::delete('/domains/{id}', [DomainSetupController::class, 'deleteDomain']);

                Route::prefix('email-settings')->group(function () {
                    Route::get('/', [EmailSettingController::class, 'index']);
                    Route::post('/', [EmailSettingController::class, 'store']);
                });
                //firebase route
                Route::prefix('firebase-settings')->group(function () {
                    Route::get('/', [FirebaseSettingController::class, 'index']);
                    Route::post('/', [FirebaseSettingController::class, 'store']);
                });
                //currencies routes
                Route::prefix('currencies')->group(function () {
                    Route::get('/', [CurrencyController::class, 'index']);
                    Route::post('/', [CurrencyController::class, 'store']);
                    Route::get('/{id}', [CurrencyController::class, 'show']);
                    Route::post('/update/{id}', [CurrencyController::class, 'update']);
                    Route::delete('/{id}', [CurrencyController::class, 'destroy']);
                    Route::get('{id}/restore', [CurrencyController::class, 'restore']);
                    Route::delete('{id}/force', [CurrencyController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [CurrencyController::class, 'toggleStatus']);
                });
                //steadfast routes
                Route::prefix('steadfast')->group(function () {
                    Route::get('/', [SteadfastOrderController::class, 'index']);
                    Route::post('/', [SteadfastOrderController::class, 'store']);
                    Route::post('/bulk-store', [SteadfastOrderController::class, 'bulkStore']);
                    Route::get('/update-status/{id}', [SteadfastOrderController::class, 'updateStatus']);
                    Route::post('/bulk-update-status', [SteadfastOrderController::class, 'updateBulkStatus']);
                });

                //pathao routes
                Route::prefix('pathao')->group(function () {
                    Route::get('/', [PathaoController::class, 'index']);
                    Route::post('/', [PathaoController::class, 'store']);
                    Route::post('/bulk-store', [PathaoController::class, 'bulkStore']);
                    Route::get('/update-status/{id}', [PathaoController::class, 'updateStatus']);
                    Route::post('/bulk-update-status', [PathaoController::class, 'updateBulkStatus']);
                    Route::get('/token', [PathaoController::class, 'testPathaoToken']);
                    Route::get('/cities', [PathaoController::class, 'getCities']);
                    Route::get('/zones/{cityId}', [PathaoController::class, 'getZones']);
                    Route::get('/areas/{zoneId}', [PathaoController::class, 'getAreas']);
                });
                //taxrates routes
                Route::prefix('taxrates')->group(function () {
                    Route::get('/', [TaxRateController::class, 'index']);
                    Route::post('/', [TaxRateController::class, 'store']);
                    Route::get('/{id}', [TaxRateController::class, 'show']);
                    Route::post('/update/{id}', [TaxRateController::class, 'update']);
                    Route::delete('/{id}', [TaxRateController::class, 'destroy']);
                    Route::get('{id}/restore', [TaxRateController::class, 'restore']);
                    Route::delete('{id}/force', [TaxRateController::class, 'forceDestroy']);
                });
                //taxgroups routes
                Route::prefix('taxgroups')->group(function () {
                    Route::get('/', [TaxGroupController::class, 'index']);
                    Route::post('/', [TaxGroupController::class, 'store']);
                    Route::get('/{id}', [TaxGroupController::class, 'show']);
                    Route::post('/update/{id}', [TaxGroupController::class, 'update']);
                    Route::patch('/{id}/toggle-status', [TaxGroupController::class, 'toggleStatus']);
                    Route::delete('/{id}', [TaxGroupController::class, 'destroy']);
                    Route::get('{id}/restore', [TaxGroupController::class, 'restore']);
                    Route::delete('{id}/force', [TaxGroupController::class, 'forceDestroy']);
                });

                Route::get('/account-types', [AccountGroupController::class, 'accountType']);
                //account-groups routes
                Route::prefix('account-groups')->group(function () {
                    Route::get('/', [AccountGroupController::class, 'index']);
                    Route::post('/', [AccountGroupController::class, 'store']);
                    Route::get('/{id}', [AccountGroupController::class, 'show']);
                    Route::post('/update/{id}', [AccountGroupController::class, 'update']);
                    Route::delete('/{id}', [AccountGroupController::class, 'destroy']);
                    Route::get('{id}/restore', [AccountGroupController::class, 'restore']);
                    Route::delete('{id}/force', [AccountGroupController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [AccountGroupController::class, 'toggleStatus']);
                });
                //account-charts routes
                Route::prefix('account-charts')->group(function () {
                    Route::get('/', [ChartOfAccountController::class, 'index']);
                    Route::post('/', [ChartOfAccountController::class, 'store']);
                    Route::get('/{id}', [ChartOfAccountController::class, 'show']);
                    Route::post('/update/{id}', [ChartOfAccountController::class, 'update']);
                    Route::delete('/{id}', [ChartOfAccountController::class, 'destroy']);
                    Route::get('{id}/restore', [ChartOfAccountController::class, 'restore']);
                    Route::delete('{id}/force', [ChartOfAccountController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [ChartOfAccountController::class, 'toggleStatus']);
                });
                //account-expenses routes
                Route::prefix('account-expenses')->group(function () {
                    Route::get('/', [TransactionExpenseController::class, 'index']);
                    Route::post('/', [TransactionExpenseController::class, 'store']);
                    Route::get('/{id}', [TransactionExpenseController::class, 'show']);
                    Route::post('/update/{id}', [TransactionExpenseController::class, 'update']);
                    Route::delete('/{id}', [TransactionExpenseController::class, 'destroy']);
                    Route::get('{id}/restore', [TransactionExpenseController::class, 'restore']);
                    Route::delete('{id}/force', [TransactionExpenseController::class, 'forceDestroy']);
                    Route::patch('/{id}/update-status', [TransactionExpenseController::class, 'updateStatus']);
                });
                //account-incomes routes
                Route::prefix('account-incomes')->group(function () {
                    Route::get('/', [TransactionIncomeController::class, 'index']);
                    Route::post('/', [TransactionIncomeController::class, 'store']);
                    Route::get('/{id}', [TransactionIncomeController::class, 'show']);
                    Route::post('/update/{id}', [TransactionIncomeController::class, 'update']);
                    Route::delete('/{id}', [TransactionIncomeController::class, 'destroy']);
                    Route::get('{id}/restore', [TransactionIncomeController::class, 'restore']);
                    Route::delete('{id}/force', [TransactionIncomeController::class, 'forceDestroy']);
                    Route::patch('/{id}/update-status', [TransactionIncomeController::class, 'updateStatus']);
                });

                //account-journals routes
                Route::prefix('account-journals')->group(function () {
                    Route::get('/', [TransactionJournalController::class, 'index']);
                    Route::post('/', [TransactionJournalController::class, 'store']);
                    Route::get('/{id}', [TransactionJournalController::class, 'show']);
                    Route::post('/update/{id}', [TransactionJournalController::class, 'update']);
                    Route::delete('/{id}', [TransactionJournalController::class, 'destroy']);
                    Route::get('{id}/restore', [TransactionJournalController::class, 'restore']);
                    Route::delete('{id}/force', [TransactionJournalController::class, 'forceDestroy']);
                    Route::patch('/{id}/update-status', [TransactionJournalController::class, 'updateStatus']);
                });

                //account-transfers routes
                Route::prefix('account-transfers')->group(function () {
                    Route::get('/', [TransactionInternalTransferController::class, 'index']);
                    Route::post('/', [TransactionInternalTransferController::class, 'store']);
                    Route::get('/{id}', [TransactionInternalTransferController::class, 'show']);
                    Route::post('/update/{id}', [TransactionInternalTransferController::class, 'update']);
                    Route::delete('/{id}', [TransactionInternalTransferController::class, 'destroy']);
                    Route::get('{id}/restore', [TransactionInternalTransferController::class, 'restore']);
                    Route::delete('{id}/force', [TransactionInternalTransferController::class, 'forceDestroy']);
                    Route::patch('/{id}/update-status', [TransactionInternalTransferController::class, 'updateStatus']);
                });

                // Accounting Settings & Mode Switcher
                Route::prefix('accounting-settings')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Api\AccountingSettingController::class, 'show']);
                    Route::post('/', [\App\Http\Controllers\Api\AccountingSettingController::class, 'update']);
                    Route::post('/seed-defaults', [\App\Http\Controllers\Api\AccountingSettingController::class, 'seedDefaults']);
                    Route::post('/close-fy', [\App\Http\Controllers\Api\AccountingSettingController::class, 'closeFinancialYear']);
                });

                // Finance & Accounting Dashboard
                Route::prefix('accounting-dashboard')->group(function () {
                    Route::get('stats', [\App\Http\Controllers\Api\AccountingDashboardController::class, 'stats']);
                    Route::get('charts', [\App\Http\Controllers\Api\AccountingDashboardController::class, 'charts']);
                    Route::get('recent-activity', [\App\Http\Controllers\Api\AccountingDashboardController::class, 'recentActivity']);
                    Route::get('order-profitability', [\App\Http\Controllers\Api\AccountingDashboardController::class, 'orderProfitability']);
                });

                // TallyPrime Accounting Reports
                Route::prefix('accounting-reports')->group(function () {
                    Route::get('day-book', [\App\Http\Controllers\Api\AccountingReportController::class, 'dayBook']);
                    Route::get('general-ledger', [\App\Http\Controllers\Api\AccountingReportController::class, 'generalLedger']);
                    Route::get('trial-balance', [\App\Http\Controllers\Api\AccountingReportController::class, 'trialBalance']);
                    Route::get('profit-loss', [\App\Http\Controllers\Api\AccountingReportController::class, 'profitAndLoss']);
                    Route::get('balance-sheet', [\App\Http\Controllers\Api\AccountingReportController::class, 'balanceSheet']);
                });

                // Enterprise Risk Management & Credit Controls
                Route::prefix('risk-management')->group(function () {
                    Route::get('overview', [\App\Http\Controllers\Api\RiskManagementController::class, 'overview']);
                    Route::get('customers/{id}/profile', [\App\Http\Controllers\Api\RiskManagementController::class, 'customerProfile']);
                    Route::get('check-credit', [\App\Http\Controllers\Api\RiskManagementController::class, 'checkCredit']);
                    Route::get('dead-stock', [\App\Http\Controllers\Api\RiskManagementController::class, 'deadStock']);
                    Route::get('liquidity', [\App\Http\Controllers\Api\RiskManagementController::class, 'liquidity']);
                    Route::get('settings', [\App\Http\Controllers\Api\RiskManagementController::class, 'getSettings']);
                    Route::post('settings', [\App\Http\Controllers\Api\RiskManagementController::class, 'updateSettings']);
                });
                //recurring-journals routes
                Route::prefix('recurring-journals')->group(function () {
                    Route::get('/', [RecurringJournalController::class, 'index']);
                    Route::post('/', [RecurringJournalController::class, 'store']);
                    Route::get('/{id}', [RecurringJournalController::class, 'show']);
                    Route::post('/update/{id}', [RecurringJournalController::class, 'update']);
                    Route::delete('/{id}', [RecurringJournalController::class, 'destroy']);
                    Route::get('{id}/restore', [RecurringJournalController::class, 'restore']);
                    Route::delete('{id}/force', [RecurringJournalController::class, 'forceDestroy']);
                    Route::patch('/{id}/update-status', [RecurringJournalController::class, 'updateApprovalStatus']);
                    Route::patch('/{id}/toggle-status', [RecurringJournalController::class, 'toggleStatus']);
                });
                //asset-categories routes
                Route::prefix('asset-categories')->group(function () {
                    Route::get('/', [AssetCategoryController::class, 'index']);
                    Route::post('/', [AssetCategoryController::class, 'store']);
                    Route::get('/{id}', [AssetCategoryController::class, 'show']);
                    Route::post('/update/{id}', [AssetCategoryController::class, 'update']);
                    Route::delete('/{id}', [AssetCategoryController::class, 'destroy']);
                    Route::get('{id}/restore', [AssetCategoryController::class, 'restore']);
                    Route::delete('{id}/force', [AssetCategoryController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [AssetCategoryController::class, 'toggleStatus']);
                });
                //asset-categories routes
                Route::prefix('assets')->group(function () {
                    Route::get('/', [AssetController::class, 'index']);
                    Route::post('/', [AssetController::class, 'store']);
                    Route::get('/{id}', [AssetController::class, 'show']);
                    Route::post('/update/{id}', [AssetController::class, 'update']);
                    Route::delete('/{id}', [AssetController::class, 'destroy']);
                    Route::get('{id}/restore', [AssetController::class, 'restore']);
                    Route::delete('{id}/force', [AssetController::class, 'forceDestroy']);
                    Route::patch('/{id}/update-status', [AssetController::class, 'updateStatus']);
                });
                //asset-purchases routes
                Route::prefix('asset-purchases')->group(function () {
                    Route::get('/', [AssetPurchaseController::class, 'index']);
                    Route::post('/', [AssetPurchaseController::class, 'store']);
                    Route::get('/{id}', [AssetPurchaseController::class, 'show']);
                    Route::post('/update/{id}', [AssetPurchaseController::class, 'update']);
                    Route::delete('/{id}', [AssetPurchaseController::class, 'destroy']);
                    Route::get('{id}/restore', [AssetPurchaseController::class, 'restore']);
                    Route::delete('{id}/force', [AssetPurchaseController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [AssetPurchaseController::class, 'toggleStatus']);
                });
                //asset-purchases routes
                Route::prefix('asset-depreciations')->group(function () {
                    Route::get('/', [AssetDepreciationController::class, 'index']);
                    Route::post('/', [AssetDepreciationController::class, 'store']);
                    Route::get('/{id}', [AssetDepreciationController::class, 'show']);
                    Route::post('/update/{id}', [AssetDepreciationController::class, 'update']);
                    Route::delete('/{id}', [AssetDepreciationController::class, 'destroy']);
                    Route::get('{id}/restore', [AssetDepreciationController::class, 'restore']);
                    Route::delete('{id}/force', [AssetDepreciationController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [AssetDepreciationController::class, 'toggleStatus']);
                });
                //disposal-types routes
                Route::prefix('disposal-types')->group(function () {
                    Route::get('/', [DisposalTypeController::class, 'index']);
                    Route::post('/', [DisposalTypeController::class, 'store']);
                    Route::get('/{id}', [DisposalTypeController::class, 'show']);
                    Route::post('/update/{id}', [DisposalTypeController::class, 'update']);
                    Route::delete('/{id}', [DisposalTypeController::class, 'destroy']);
                    Route::get('{id}/restore', [DisposalTypeController::class, 'restore']);
                    Route::delete('{id}/force', [DisposalTypeController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [DisposalTypeController::class, 'toggleStatus']);
                });
                //asset-disposals routes
                Route::prefix('asset-disposals')->group(function () {
                    Route::get('/', [AssetDisposalController::class, 'index']);
                    Route::post('/', [AssetDisposalController::class, 'store']);
                    Route::get('/{id}', [AssetDisposalController::class, 'show']);
                    Route::post('/update/{id}', [AssetDisposalController::class, 'update']);
                    Route::delete('/{id}', [AssetDisposalController::class, 'destroy']);
                    Route::get('{id}/restore', [AssetDisposalController::class, 'restore']);
                    Route::delete('{id}/force', [AssetDisposalController::class, 'forceDestroy']);
                    Route::patch('/{id}/update-status', [AssetDisposalController::class, 'updateStatus']);
                });
                //sms-templates routes
                Route::prefix('sms-templates')->group(function () {
                    Route::get('/', [SmsTemplateController::class, 'index']);
                    Route::post('/', [SmsTemplateController::class, 'store']);
                    Route::get('/{id}', [SmsTemplateController::class, 'show']);
                    Route::post('/update/{id}', [SmsTemplateController::class, 'update']);
                    Route::delete('/{id}', [SmsTemplateController::class, 'destroy']);
                    Route::get('{id}/restore', [SmsTemplateController::class, 'restore']);
                    Route::delete('{id}/force', [SmsTemplateController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [SmsTemplateController::class, 'toggleStatus']);
                });
                //sms-sends routes
                Route::prefix('sms-sends')->group(function () {
                    Route::get('/', [SmsSendController::class, 'index']);
                    Route::post('/', [SmsSendController::class, 'store']);
                    Route::get('/{id}', [SmsSendController::class, 'show']);
                });
                //email-templates routes
                Route::prefix('email-templates')->group(function () {
                    Route::get('/', [EmailTemplateController::class, 'index']);
                    Route::post('/', [EmailTemplateController::class, 'store']);
                    Route::get('/{id}', [EmailTemplateController::class, 'show']);
                    Route::post('/update/{id}', [EmailTemplateController::class, 'update']);
                    Route::delete('/{id}', [EmailTemplateController::class, 'destroy']);
                    Route::get('{id}/restore', [EmailTemplateController::class, 'restore']);
                    Route::delete('{id}/force', [EmailTemplateController::class, 'forceDestroy']);
                    Route::patch('/{id}/toggle-status', [EmailTemplateController::class, 'toggleStatus']);
                });
                //email-sends routes
                Route::prefix('email-sends')->group(function () {
                    Route::get('/', [EmailSendController::class, 'index']);
                    Route::post('/', [EmailSendController::class, 'store']);
                    Route::get('/{id}', [EmailSendController::class, 'show']);
                });
                Route::get('/inbox/channels', [ChannelController::class, 'index']);
                Route::get('/inbox/integrated/channels', [ChannelController::class, 'getChannel']);
                Route::post('/inbox/channels', [ChannelController::class, 'store']);
                Route::put('/inbox/channels/{channel}', [ChannelController::class, 'update']);
                Route::delete('/inbox/channels/{channel}', [ChannelController::class, 'destroy']);
                Route::get('/inbox/labels', [ChannelController::class, 'label']);
                Route::post('/inbox/labels', [ChannelController::class, 'labelStore']);
                Route::put('/inbox/labels/{label}', [ChannelController::class, 'labelUpdate']);
                Route::delete('/inbox/labels/{label}', [ChannelController::class, 'labelDestroy']);

                Route::get('/inbox/conversations', [ConversationController::class, 'index']);
                Route::post('/inbox/conversations/{conversation}/assign', [ConversationController::class, 'assign']);
                Route::get('/inbox/conversations/{conversation}/messages', [ConversationController::class, 'messages']);
                Route::post('/inbox/conversations/{conversation}/messages', [ConversationController::class, 'sendMessage']);

                Route::get('/inbox/settings', [OmniSettingsController::class, 'getSettings']);
                Route::post('/inbox/settings', [OmniSettingsController::class, 'saveSettings']);
                Route::post('/inbox/quick-replies', [OmniSettingsController::class, 'storeQuickReply']);
                Route::delete('/inbox/quick-replies/{id}', [OmniSettingsController::class, 'destroyQuickReply']);
                Route::get('/inbox/parties/{id}', [CustomerPanelController::class, 'show']);
                Route::post('/inbox/parties/{party}/labels', [CustomerPanelController::class, 'addLabel']);
                Route::post('/inbox/parties/{party}/notes', [CustomerPanelController::class, 'addNote']);
                Route::put('/inbox/crm-notes/{id}', [CustomerPanelController::class, 'noteUpdate']);
                Route::delete('/inbox/crm-notes/{id}', [CustomerPanelController::class, 'noteDestroy']);

                Route::post('/inbox/channels/direct-connect', [MetaConnectController::class, 'directConnect']);
                // routes/api.php
                Route::prefix('/inbox/meta')->group(function () {
                    Route::get('/login/{type}', [MetaConnectController::class, 'startLogin']);
                    Route::get('/session/{sessionId}/businesses', [MetaConnectController::class, 'businesses']);
                    Route::get('/session/{sessionId}/pages', [MetaConnectController::class, 'pages']);
                    Route::post('/session/{sessionId}/connect-page', [MetaConnectController::class, 'connectPage']);
                    Route::get('/session/{sessionId}/whatsapp-accounts', [MetaConnectController::class, 'whatsappAccounts']);
                    Route::post('/session/{sessionId}/connect-whatsapp', [MetaConnectController::class, 'connectWhatsapp']);
                    Route::delete('/connections/{connection}', [MetaConnectController::class, 'disconnect']);
                });
                Route::get('/inbox/facebook/{channelId}/conversations', [MetaDirectProxyController::class, 'getConversations']);
                Route::get('/inbox/facebook/{channelId}/threads/{threadId}/messages', [MetaDirectProxyController::class, 'getMessages']);
                Route::post('/inbox/facebook/{channelId}/threads/{threadId}/send', [MetaDirectProxyController::class, 'sendMessage']);
                Route::post('/inbox/facebook/{channelId}/threads/{threadId}/mark-seen', [MetaDirectProxyController::class, 'markSeen']);
                Route::put('/inbox/facebook/conversations/{threadId}/archive', [MetaDirectProxyController::class, 'archive']);
                Route::post('channels/{channelId}/threads/{threadId}/assign', [MetaDirectProxyController::class, 'assignUser']);
                Route::post('channels/{channelId}/threads/{threadId}/unassign', [MetaDirectProxyController::class, 'unassignUser']);
                Route::get('/inbox/meta/sessions/{sessionId}/pages', [MetaConnectController::class, 'getPages']);
                Route::get('/link-preview', [MetaDirectProxyController::class, 'fetchLinkPreview']);
                Route::post('/inbox/meta/sessions/{sessionId}/connect-channels', [MetaConnectController::class, 'connectChannels']);
                //billing
                Route::prefix('billing')->group(function () {
                    Route::get('/{id}', [BillingController::class, 'billingReports']);
                });
                // User Routes
                Route::prefix('users')->group(function () {
                    Route::get('/', [UserController::class, 'index']);
                    Route::post('/', [UserController::class, 'store']);
                    Route::get('/{id}', [UserController::class, 'show']);
                    Route::post('/update/{id}', [UserController::class, 'update']);
                    Route::delete('/{id}', [UserController::class, 'destroy']);
                    Route::patch('/{id}/toggle-status', [UserController::class, 'toggleStatus']);
                });
                Route::post('users/{user}/password-change-request', [UserPasswordController::class, 'changePassword']);
                Route::prefix('bulk')->group(function () {
                    Route::patch('{resource}/status', [BulkActionController::class, 'updateStatus']);
                    Route::delete('{resource}/delete', [BulkActionController::class, 'bulkDelete']);
                    Route::delete('{resource}/force-delete', [BulkActionController::class, 'bulkForceDelete']);
                    Route::patch('{resource}/restore',      [BulkActionController::class, 'bulkRestore']);
                });
                Route::prefix('content-settings')->group(function () {
                    Route::get('/',                         [ContentSettingController::class, 'index']);
                    Route::get('/{id}',                     [ContentSettingController::class, 'show']);
                    Route::post('/',                        [ContentSettingController::class, 'store']);
                    Route::post('/update/{id}',                     [ContentSettingController::class, 'update']);
                    Route::delete('/{id}',                  [ContentSettingController::class, 'destroy']);
                    Route::patch('/{id}/toggle-status',     [ContentSettingController::class, 'toggleStatus']);
                    Route::patch('/reorder',                [ContentSettingController::class, 'reorder']);
                });
                //sms
                Route::get('/sms/wallet', [SmsWalletController::class, 'wallet']);
                Route::get('/sms/packages', [SmsWalletController::class, 'packages']);
                Route::post('/sms/recharge', [SmsWalletController::class, 'requestRecharge']);
                Route::get('/sms/recharge/history', [SmsWalletController::class, 'rechargeHistory']);
                Route::get('/sms/transactions', [SmsWalletController::class, 'transactions']);
                // Customer Groups Routes
                Route::prefix('customer-groups')->controller(CustomerGroupController::class)->group(function () {

                    Route::get('criteria', 'fetchCustomersByCriteria');
                    // Standard Routes
                    Route::get('/', 'index');
                    Route::post('/', 'store');
                    Route::get('{id}', 'show');
                    Route::delete('{id}/customers/{customerId}', 'removeCustomer');
                    Route::patch('{id}/toggle-status', 'toggleStatus');
                    Route::delete('{id}', 'destroy');
                });
                // Product Groups Routes
                Route::prefix('product-groups')->controller(ProductGroupController::class)->group(function () {

                    Route::get('criteria', 'fetchProductsByCriteria');
                    // Standard Routes
                    Route::get('/', 'index');
                    Route::post('/', 'store');
                    Route::get('{id}', 'show');
                    Route::delete('{id}/products/{productId}', 'removeProduct');
                    Route::patch('{id}/toggle-status', 'toggleStatus');
                    Route::patch('{id}/toggle-frontend', 'toggleFrontend');
                    Route::delete('{id}', 'destroy');
                });
                Route::prefix('roles')->group(function () {
                    Route::get('/', [RoleController::class, 'index']);
                    Route::post('/', [RoleController::class, 'store']);
                    Route::get('/{id}', [RoleController::class, 'show']);
                    Route::post('/update/{id}', [RoleController::class, 'update']);
                    Route::delete('/{id}', [RoleController::class, 'destroy']);
                    Route::post('/{id}/assign-users', [RoleController::class, 'assignUsers']);
                    Route::post('/{id}/remove-user',  [RoleController::class, 'removeUser']);
                });

                Route::get('/permissions', [PermissionController::class, 'index']);
                Route::get('/reports/profit-loss', [ProfitLossReportController::class, 'generate']);
                Route::get('/reports/sales', [SalesReportController::class, 'generate']);
                Route::get('/reports/product-wise-sales', [ProductWiseSalesReportController::class, 'generate']);
                Route::get('/reports/customer', [CustomerReportController::class, 'generate']);
                Route::get('/reports/courier', [\App\Http\Controllers\Api\CourierReportController::class, 'generate']);
                Route::get('/reports/cancellation', [\App\Http\Controllers\Api\CancellationReportController::class, 'generate']);
                Route::get('/reports/marketing-roi', [\App\Http\Controllers\Api\MarketingRoiReportController::class, 'generate']);
                Route::get('/reports/abandoned-cart', [\App\Http\Controllers\Api\AbandonedCartReportController::class, 'generate']);

                Route::get('/dashboard/overview', [DashboardController::class, 'overview']);
                Route::get('/dashboard/full-report', [DashboardController::class, 'fullReport']);
                Route::get('/reports/balance-sheet', [BalanceSheetReportController::class, 'generate']);
                Route::get('/reports/inventory-stock', [InventoryStockReportController::class, 'generate']);

                // AI Integration Routes
                Route::get('/settings/ai', [\App\Http\Controllers\Api\AiSettingController::class, 'show']);
                Route::post('/settings/ai', [\App\Http\Controllers\Api\AiSettingController::class, 'update']);
                Route::post('/settings/ai/test', [\App\Http\Controllers\Api\AiSettingController::class, 'testConnection']);
                Route::post('/ai/generate', [\App\Http\Controllers\Api\AIGeneratorController::class, 'generate']);

                // ==========================================
                // Production / Manufacturing Routes
                // ==========================================
                Route::prefix('production')->group(function () {
                    // Dashboard
                    Route::get('/dashboard', [ProductionDashboardController::class, 'stats']);
                    Route::get('/dashboard-stats', [ProductionDashboardController::class, 'stats']);
                    Route::get('/dashboard-charts', [ProductionDashboardController::class, 'charts']);

                    // Work Centers
                    Route::patch('work-centers/{id}/toggle-status', [WorkCenterController::class, 'toggleStatus']);
                    Route::apiResource('work-centers', WorkCenterController::class);

                    // Production Stages
                    Route::post('stages/reorder', [ProductionStageController::class, 'reorder']);
                    Route::apiResource('stages', ProductionStageController::class);

                    // Bills of Materials (BOM)
                    Route::post('boms/{id}/clone', [BillOfMaterialController::class, 'clone']);
                    Route::post('boms/{id}/calculate-cost', [BillOfMaterialController::class, 'calculateCost']);
                    Route::apiResource('boms', BillOfMaterialController::class);

                    // Production Planning
                    Route::post('plans/{id}/convert-to-order', [ProductionPlanningController::class, 'convertToOrder']);
                    Route::apiResource('plans', ProductionPlanningController::class);
                    Route::apiResource('planning', ProductionPlanningController::class);

                    // Production Orders
                    Route::post('orders/check-stock', [ProductionOrderController::class, 'checkStock']);
                    Route::get('orders/{id}/check-stock', [ProductionOrderController::class, 'checkStock']);
                    Route::post('orders/{id}/start', [ProductionOrderController::class, 'start']);
                    Route::post('orders/{id}/progress-stage', [ProductionOrderController::class, 'progressStage']);
                    Route::post('orders/{id}/complete-stage', [ProductionOrderController::class, 'completeStage']);
                    Route::post('orders/{id}/consume-materials', [ProductionOrderController::class, 'consumeMaterials']);
                    Route::post('orders/{id}/log-output', [ProductionOrderController::class, 'logOutput']);
                    Route::post('orders/{id}/pause', [ProductionOrderController::class, 'pause']);
                    Route::post('orders/{id}/resume', [ProductionOrderController::class, 'resume']);
                    Route::post('orders/{id}/complete', [ProductionOrderController::class, 'complete']);
                    Route::post('orders/{id}/cancel', [ProductionOrderController::class, 'cancel']);
                    Route::apiResource('orders', ProductionOrderController::class);

                    // Wastages
                    Route::apiResource('wastages', ProductionWastageController::class);

                    // Quality Control (QC)
                    Route::apiResource('quality-checks', ProductionQualityCheckController::class);

                    // Production Costs
                    Route::get('costs', [ProductionCostController::class, 'index']);
                    Route::post('costs', [ProductionCostController::class, 'store']);
                    Route::get('costs/order/{orderId}', [ProductionCostController::class, 'byOrder']);
                    Route::get('costs/{id}', [ProductionCostController::class, 'show']);

                    // Reports
                    Route::get('reports/summary', [ProductionReportController::class, 'summary']);
                    Route::get('reports/efficiency', [ProductionReportController::class, 'efficiency']);
                    Route::get('reports/wastage', [ProductionReportController::class, 'wastage']);
                    Route::get('reports/cost-analysis', [ProductionReportController::class, 'costAnalysis']);
                    Route::get('reports/production', [ProductionReportController::class, 'productionReport']);
                    Route::get('reports/material-consumption', [ProductionReportController::class, 'materialConsumptionReport']);
                    Route::get('reports/costs', [ProductionReportController::class, 'costReport']);

                    // Settings
                    Route::get('settings', [ProductionSettingController::class, 'index']);
                    Route::post('settings', [ProductionSettingController::class, 'store']);
                });

                // ==========================================
                // Project Management Routes
                // ==========================================
                Route::prefix('projects')->group(function () {
                    // Dashboard & Monitoring
                    Route::get('dashboard-stats', [\App\Http\Controllers\Api\Project\ProjectDashboardController::class, 'stats']);
                    Route::get('dashboard-charts', [\App\Http\Controllers\Api\Project\ProjectDashboardController::class, 'charts']);
                    Route::get('monitoring', [\App\Http\Controllers\Api\Project\ProjectMonitoringController::class, 'index']);
                    Route::get('task-reports', [\App\Http\Controllers\Api\Project\ProjectTaskReportController::class, 'index']);
                    Route::apiResource('task-statuses', \App\Http\Controllers\Api\Project\ProjectTaskStatusController::class);

                    // Tasks
                    Route::post('tasks/bulk', [\App\Http\Controllers\Api\Project\ProjectTaskController::class, 'bulkStore']);
                    Route::post('tasks/reorder', [\App\Http\Controllers\Api\Project\ProjectTaskController::class, 'reorder']);
                    Route::post('tasks/{id}/toggle-complete', [\App\Http\Controllers\Api\Project\ProjectTaskController::class, 'toggleComplete']);
                    Route::post('tasks/{id}/start-timer', [\App\Http\Controllers\Api\Project\ProjectTaskController::class, 'startTimer']);
                    Route::post('tasks/{id}/stop-timer', [\App\Http\Controllers\Api\Project\ProjectTaskController::class, 'stopTimer']);
                    Route::get('tasks/{id}/status-histories', [\App\Http\Controllers\Api\Project\ProjectTaskController::class, 'statusHistories']);
                    Route::apiResource('tasks', \App\Http\Controllers\Api\Project\ProjectTaskController::class);

                    // Phases
                    Route::apiResource('phases', \App\Http\Controllers\Api\Project\ProjectPhaseController::class);

                    // Milestones
                    Route::apiResource('milestones', \App\Http\Controllers\Api\Project\ProjectMilestoneController::class);

                    // Team Members
                    Route::apiResource('members', \App\Http\Controllers\Api\Project\ProjectTeamController::class);

                    // Time Tracking
                    Route::apiResource('time-entries', \App\Http\Controllers\Api\Project\ProjectTimeController::class);

                    // Costing
                    Route::get('costs', [\App\Http\Controllers\Api\Project\ProjectCostController::class, 'index']);
                    Route::post('costs/labor', [\App\Http\Controllers\Api\Project\ProjectCostController::class, 'storeLabor']);
                    Route::delete('costs/labor/{id}', [\App\Http\Controllers\Api\Project\ProjectCostController::class, 'destroyLabor']);
                    Route::post('costs/materials', [\App\Http\Controllers\Api\Project\ProjectCostController::class, 'storeMaterial']);
                    Route::delete('costs/materials/{id}', [\App\Http\Controllers\Api\Project\ProjectCostController::class, 'destroyMaterial']);
                    Route::post('costs/equipment', [\App\Http\Controllers\Api\Project\ProjectCostController::class, 'storeEquipment']);
                    Route::delete('costs/equipment/{id}', [\App\Http\Controllers\Api\Project\ProjectCostController::class, 'destroyEquipment']);
                    Route::post('costs/expenses', [\App\Http\Controllers\Api\Project\ProjectCostController::class, 'storeExpense']);
                    Route::delete('costs/expenses/{id}', [\App\Http\Controllers\Api\Project\ProjectCostController::class, 'destroyExpense']);
                    Route::post('costs/overheads', [\App\Http\Controllers\Api\Project\ProjectCostController::class, 'storeOverhead']);
                    Route::delete('costs/overheads/{id}', [\App\Http\Controllers\Api\Project\ProjectCostController::class, 'destroyOverhead']);
                    Route::post('{projectId}/budgets', [\App\Http\Controllers\Api\Project\ProjectCostController::class, 'saveBudgets']);

                    // Revenue
                    Route::apiResource('revenues', \App\Http\Controllers\Api\Project\ProjectRevenueController::class);

                    // Documents
                    Route::apiResource('documents', \App\Http\Controllers\Api\Project\ProjectDocumentController::class);

                    // Discussions
                    Route::apiResource('discussions', \App\Http\Controllers\Api\Project\ProjectDiscussionController::class);

                    // Reports
                    Route::get('reports/summary', [\App\Http\Controllers\Api\Project\ProjectReportController::class, 'summary']);
                    Route::get('reports/materials', [\App\Http\Controllers\Api\Project\ProjectReportController::class, 'materials']);
                    Route::get('reports/labor', [\App\Http\Controllers\Api\Project\ProjectReportController::class, 'labor']);
                    Route::get('reports/profitability', [\App\Http\Controllers\Api\Project\ProjectReportController::class, 'profitability']);

                    // Templates
                    Route::apiResource('templates', \App\Http\Controllers\Api\Project\ProjectTemplateController::class);

                    // Settings
                    Route::get('settings', [\App\Http\Controllers\Api\Project\ProjectSettingController::class, 'show']);
                    Route::post('settings', [\App\Http\Controllers\Api\Project\ProjectSettingController::class, 'store']);

                    // Core Projects CRUD & Template Action
                    Route::post('{id}/apply-template', [\App\Http\Controllers\Api\Project\ProjectController::class, 'applyTemplate']);
                    Route::get('/', [\App\Http\Controllers\Api\Project\ProjectController::class, 'index']);
                    Route::post('/', [\App\Http\Controllers\Api\Project\ProjectController::class, 'store']);
                    Route::get('/{id}', [\App\Http\Controllers\Api\Project\ProjectController::class, 'show']);
                    Route::put('/{id}', [\App\Http\Controllers\Api\Project\ProjectController::class, 'update']);
                    Route::delete('/{id}', [\App\Http\Controllers\Api\Project\ProjectController::class, 'destroy']);
                });
            });
        });
        //pricing plan
        Route::middleware(['auth:sanctum', 'super_admin'])->group(function () {
            Route::prefix('pricing-plan')->group(function () {
                Route::get('/', [PricingController::class, 'index']);
                Route::post('/', [PricingController::class, 'store']);
                Route::get('/{id}', [PricingController::class, 'show']);
                Route::post('/update/{id}', [PricingController::class, 'update']);
                Route::delete('/{id}', [PricingController::class, 'destroy']);
                Route::get('/{id}/restore', [PricingController::class, 'restore']);
                Route::delete('/{id}/force', [PricingController::class, 'forceDestroy']);
                Route::patch('/{id}/toggle-status', [PricingController::class, 'toggleStatus']);
            });
            Route::prefix('pricing-packages')->group(function () {
                Route::post('/', [PricingPackageController::class, 'store']);
                Route::get('/{id}', [PricingPackageController::class, 'show']);
                Route::post('/update/{id}', [PricingPackageController::class, 'update']);
                Route::delete('/{id}', [PricingPackageController::class, 'destroy']);
                Route::get('/{id}/restore', [PricingPackageController::class, 'restore']);
                Route::delete('/{id}/force', [PricingPackageController::class, 'forceDestroy']);
                Route::patch('/{id}/toggle-status', [PricingPackageController::class, 'toggleStatus']);
            });

            Route::get('/reminder-settings', [ReminderSettingController::class, 'index']);
            Route::post('/reminder-settings', [ReminderSettingController::class, 'store']);
            Route::put('/reminder-settings/{reminderSetting}', [ReminderSettingController::class, 'update']);
            Route::delete('/reminder-settings/{reminderSetting}', [ReminderSettingController::class, 'destroy']);


            Route::apiResource('/sms-packages', SmsPackageController::class);
            Route::get('/sms-recharges', [SmsRechargeController::class, 'index']);
            Route::post('/sms-recharges/{id}/process', [SmsRechargeController::class, 'process']);

            Route::middleware(['super_admin'])->group(function () {
                Route::prefix('super-admin')->group(function () {
                    Route::get('/options/companies', [SelectOptionController::class, 'companies']);
                    Route::get('/email-sends',  [SuperAdminEmailSendController::class, 'index']);
                    Route::post('/email-sends', [SuperAdminEmailSendController::class, 'store']);
                    Route::get('/email-sends/{id}', [SuperAdminEmailSendController::class, 'show']);
                    Route::get('/sms-sends',  [SuperAdminSmsSendController::class, 'index']);
                    Route::post('/sms-sends', [SuperAdminSmsSendController::class, 'store']);
                    Route::get('/sms-sends/{id}', [SuperAdminSmsSendController::class, 'show']);
                });
                Route::get('/package-upgrades', [PackageUpgradeController::class, 'index']);
                Route::post('/package-upgrades/{id}/status-update', [PackageUpgradeController::class, 'updateStatus']);
                Route::post('/registration/register-seller', [CompanyRegistrationController::class, 'register']);

                // Referral & Partner Program Management
                Route::apiResource('/referral-groups', ReferralGroupController::class);
                Route::apiResource('/referral-partners', ReferralPartnerController::class);
                Route::get('/referral-withdrawals', [ReferralWithdrawalController::class, 'index']);
                Route::post('/referral-withdrawals/{id}/approve', [ReferralWithdrawalController::class, 'approve']);
                Route::post('/referral-withdrawals/{id}/reject', [ReferralWithdrawalController::class, 'reject']);
            });
        });
        //bkash route
        Route::prefix('bkash')->group(function () {
            Route::get('/token', [BkashController::class, 'grantToken']);
            Route::post('/create', [BkashController::class, 'createPayment']);
            Route::get('/execute', [BkashController::class, 'execute']);
            Route::get('/success', [BkashController::class, 'successPayment'])->name('bkash.success');
            Route::get('/failure', [BkashController::class, 'failurePayment'])->name('bkash.failure');
        });



        // Customer Reviews (Added)
        Route::apiResource('customer-reviews', CustomerReviewController::class);
        Route::prefix('customer-reviews/{id}')->group(function () {
            Route::post('toggle-status', [CustomerReviewController::class, 'toggleStatus']);
            Route::post('restore', [CustomerReviewController::class, 'restore']);
            Route::delete('force-delete', [CustomerReviewController::class, 'forceDestroy']);
        });
    });
});
