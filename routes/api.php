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
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\BinController;
use App\Http\Controllers\Api\BkashController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\BonusController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\BusinessPaymentMethodController;
use App\Http\Controllers\Api\CellController;
use App\Http\Controllers\Api\ChartOfAccountController;
use App\Http\Controllers\Api\CompanyController;

use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\EmployeeTypeController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\CourierController;
use App\Http\Controllers\Api\CourierMethodController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\CustomerPaymentMethodController;
use App\Http\Controllers\Api\DisposalTypeController;
use App\Http\Controllers\Api\DomainSetupController;
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
use App\Http\Controllers\Api\MiniCategoryController;
use App\Http\Controllers\Api\NoteTemplateController;
use App\Http\Controllers\Api\OfficeLocationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderNoteController;
use App\Http\Controllers\Api\OrderReturnController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\PartyController;
use App\Http\Controllers\Api\PassChangeController;
use App\Http\Controllers\Api\PathaoController;
use App\Http\Controllers\Api\PayHeadController;
use App\Http\Controllers\Api\PaymentMethodTypeController;
use App\Http\Controllers\Api\PayRollController;
use App\Http\Controllers\Api\PayRollPayHeadController;
use App\Http\Controllers\Api\PayrollSettingController;
use App\Http\Controllers\Api\PayslipController;
use App\Http\Controllers\Api\PaySlipManagerController;
use App\Http\Controllers\Api\PeriodController;
use App\Http\Controllers\Api\PeriodTypeController;
use App\Http\Controllers\Api\PositionController;
use App\Http\Controllers\Api\PosOrderController;
use App\Http\Controllers\Api\PricingController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\PurchaseReturnController;
use App\Http\Controllers\Api\QuotationController;
use App\Http\Controllers\Api\RackController;
use App\Http\Controllers\Api\RecurringJournalController;
use App\Http\Controllers\Api\RejoinController;
use App\Http\Controllers\Api\ResignationController;
use App\Http\Controllers\Api\SlideController;
use App\Http\Controllers\Api\RequisitionController;
use App\Http\Controllers\Api\ResignRuleController;
use App\Http\Controllers\Api\SalesOrderController;
use App\Http\Controllers\Api\SelectOptionController;
use App\Http\Controllers\Api\SiteSettingController;
use App\Http\Controllers\Api\SmsSendController;
use App\Http\Controllers\Api\SmsSettingController;
use App\Http\Controllers\Api\SmsTemplateController;
use App\Http\Controllers\Api\SteadfastOrderController;
use App\Http\Controllers\Api\StockAdjustmentController;
use App\Http\Controllers\Api\StockMovementRequestController;
use App\Http\Controllers\Api\SubCategoryController;
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
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\WarehouseInventoryController;
use App\Http\Controllers\Api\WocommerceSettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {

    Route::get('/ping', function () {
        return 'pong';
    });

    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);

    Route::middleware('auth:sanctum', 'company.access')->group(function () {
        //auth
        Route::get('auth/login-history', [AuthController::class, 'historyLoginAll']);
        Route::get('auth/profile', [AuthController::class, 'profile']);
        Route::post('auth/profile/update', [AuthController::class, 'updateProfile']);
        Route::post('auth/password/update', [AuthController::class, 'updatePassword']);
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/logout-all', [AuthController::class, 'logoutAll']);
        Route::delete('auth/account', [AuthController::class, 'deleteAccount']);
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
        Route::prefix('companies')->group(function () {
            Route::get('/search/query', [CompanyController::class, 'search']);
            Route::get('/active/list', [CompanyController::class, 'getActiveCompanies']);
            Route::get('/', [CompanyController::class, 'index']);
            Route::post('/', [CompanyController::class, 'store']);
            Route::get('/{id}', [CompanyController::class, 'show']);
            Route::post('/update/{id}', [CompanyController::class, 'update'])->name('company');
            Route::get('/{id}/delation-summary', [CompanyController::class, 'deletionSummary']);
            Route::get('/{id}/can-delete', [CompanyController::class, 'canDeleteCompany']);
            Route::delete('/{id}', [CompanyController::class, 'destroy']);
            Route::get('/{id}/restore', [CompanyController::class, 'restore']);
            Route::delete('/{id}/force', [CompanyController::class, 'forceDestroy']);
            Route::patch('/{id}/toggle-status', [CompanyController::class, 'toggleStatus']);
        });
        Route::prefix('options')->group(function () {
            Route::get('/warehouses', [SelectOptionController::class, 'warehouseOptions']);
            Route::get('/suppliers', [SelectOptionController::class, 'supplierOptions']);
            Route::get('/customers', [SelectOptionController::class, 'customersOptions']);
            Route::get('/get-product-by-warehouse/{warehouseId}', [SelectOptionController::class, 'getProductByWarehouse']);
            Route::get('/products', [SelectOptionController::class, 'productOptions']);
            Route::get('/purchases', [SelectOptionController::class, 'purchaseOptions']);
            Route::get('/attribute-group', [SelectOptionController::class, 'attributeGroupOptions']);
            Route::get('/mega-categories', [SelectOptionController::class, 'megaCategoryOptions']);
            Route::get('/users', [SelectOptionController::class, 'userOptions']);
            Route::get('/asset-categories', [SelectOptionController::class, 'assetCategoryOptions']);
            Route::get('/assets', [SelectOptionController::class, 'assetOptions']);
            Route::get('/disposal-types', [SelectOptionController::class, 'disposalTypeOptions']);
            Route::get('/account-groups', [SelectOptionController::class, 'accountGroupOptions']);
            Route::get('/account-expenses', [SelectOptionController::class, 'accountExpenseOptions']);
            Route::get('/income-accounts', [SelectOptionController::class, 'incomeAccountOptions']);
            Route::get('/account-charts', [SelectOptionController::class, 'accountChartOptions']);
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
        });

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
        // Banner Routes
        Route::prefix('banners')->group(function () {
            Route::get('/', [BannerController::class, 'index']);
            Route::post('/', [BannerController::class, 'store']);
            Route::get('/{id}', [BannerController::class, 'show']);
            Route::post('/update/{id}', [BannerController::class, 'update']);
            Route::delete('/{id}', [BannerController::class, 'destroy']);
            Route::get('/{id}/restore', [BannerController::class, 'restore']);
            Route::delete('/{id}/force', [BannerController::class, 'forceDestroy']);
            Route::patch('/{id}/toggle-status', [BannerController::class, 'toggleStatus']);
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
        Route::post('/upload-image', [BlogController::class, 'uploadImage']);
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
        });

        Route::prefix('fetch-orders')->group(function () {
            Route::get('/select/order-list-option', [OrderController::class, 'getSelectListOrder']);
            Route::get('/', [OrderController::class, 'index']);
            Route::get('/{id}', [OrderController::class, 'show']);
            Route::put('/{id}/status', [OrderController::class, 'updateStatus']);
            Route::patch('/{id}/change-status', [OrderController::class, 'changeStatus']);
            Route::put('/{id}/update', [OrderController::class, 'update']);
            Route::post('/{id}/add-payment', [OrderController::class, 'addPayment']);
            Route::get('/cutomer/{customerId}', [OrderController::class, 'customerOrders']);
            Route::get('/edit-order/{id}', [OrderController::class, 'getEditOrder']);
            Route::get('/products/{id}', [OrderController::class, 'orderProducts']);
        });

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
        });
        // Templates Routes
        Route::prefix('templates')->group(function () {
            Route::get('/', [TemplateController::class, 'index']);
            Route::post('/', [TemplateController::class, 'store']);
            Route::get('/{id}', [TemplateController::class, 'show']);
            Route::get('/slug/{slug}', [TemplateController::class, 'showBySlug']);
            Route::post('/update/{id}', [TemplateController::class, 'update']);
            Route::patch('/{id}', [TemplateController::class, 'update']);
            Route::delete('/{id}', [TemplateController::class, 'destroy']);

            // Additional actions
            Route::get('/{id}/restore', [TemplateController::class, 'restore']);
            Route::delete('/{id}/force', [TemplateController::class, 'forceDestroy']);
            Route::patch('/{id}/toggle-status', [TemplateController::class, 'toggleStatus']);
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
        //site settings routes
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
            Route::patch('/{id}/toggle-status', [CustomerPaymentMethodController::class, 'toggleStatus']);
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
            Route::patch('/{id}/toggle-status', [CourierController::class, 'toggleStatus']);
        });
        //sms-settings routes
        Route::prefix('sms-settings')->group(function () {
            Route::get('/', [SmsSettingController::class, 'index']);
            Route::post('/', [SmsSettingController::class, 'store']);
            Route::get('/{id}', [SmsSettingController::class, 'show']);
            Route::post('/update/{id}', [SmsSettingController::class, 'update']);
            Route::delete('/{id}', [SmsSettingController::class, 'destroy']);
            Route::get('{id}/restore', [SmsSettingController::class, 'restore']);
            Route::delete('{id}/force', [SmsSettingController::class, 'forceDestroy']);
            Route::patch('/{id}/toggle-status', [SmsSettingController::class, 'toggleStatus']);
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
        //support-departments routes
        Route::prefix('support-departments')->group(function () {
            Route::get('/', [SupportDepartmentController::class, 'index']);
            Route::get('/{id}', [SupportDepartmentController::class, 'show']);

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

    });
    //bkash route
    Route::prefix('bkash')->group(function () {
        Route::get('/token', [BkashController::class, 'grantToken']);
        Route::post('/create', [BkashController::class, 'createPayment']);
        Route::get('/execute', [BkashController::class, 'execute']);
        Route::get('/success', [BkashController::class, 'successPayment'])->name('bkash.success');
        Route::get('/failure', [BkashController::class, 'failurePayment'])->name('bkash.failure');
    });
});
