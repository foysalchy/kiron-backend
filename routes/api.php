<?php

use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\AssignLeaveTypeController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AttributeGroupController;
use App\Http\Controllers\Api\AttributeValueController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\BinController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\BusinessPaymentMethodController;
use App\Http\Controllers\Api\CellController;
use App\Http\Controllers\Api\CompanyController;

use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\EmployeeTypeController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\CourierController;
use App\Http\Controllers\Api\CourierMethodController;
use App\Http\Controllers\Api\CustomerPaymentMethodController;
use App\Http\Controllers\Api\ExtraCategoryController;
use App\Http\Controllers\Api\HolidayController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\InventroyController;
use App\Http\Controllers\Api\JobTitleController;
use App\Http\Controllers\Api\LeaveTypeController;
use App\Http\Controllers\Api\LandingPageController;
use App\Http\Controllers\Api\LeaveApplicationController;
use App\Http\Controllers\Api\LogActionController;
use App\Http\Controllers\Api\MarketController;
use App\Http\Controllers\Api\MegaCategoryController;
use App\Http\Controllers\Api\MiniCategoryController;
use App\Http\Controllers\Api\NoteTemplateController;
use App\Http\Controllers\Api\OfficeLocationController;
use App\Http\Controllers\Api\OrderReturnController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\PartyController;
use App\Http\Controllers\Api\PassChangeController;
use App\Http\Controllers\Api\PayHeadController;
use App\Http\Controllers\Api\PaymentMethodTypeController;
use App\Http\Controllers\Api\PayRollController;
use App\Http\Controllers\Api\PayRollPayHeadController;
use App\Http\Controllers\Api\PaySlipManagerController;
use App\Http\Controllers\Api\PeriodController;
use App\Http\Controllers\Api\PeriodTypeController;
use App\Http\Controllers\Api\PositionController;
use App\Http\Controllers\Api\PosOrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\PurchaseReturnController;
use App\Http\Controllers\Api\RackController;
use App\Http\Controllers\Api\RejoinController;
use App\Http\Controllers\Api\ResignationController;
use App\Http\Controllers\Api\SlideController;
use App\Http\Controllers\Api\RequisitionController;
use App\Http\Controllers\Api\ResignRuleController;
use App\Http\Controllers\Api\SalesOrderController;
use App\Http\Controllers\Api\SiteSettingController;
use App\Http\Controllers\Api\SmsSettingController;
use App\Http\Controllers\Api\StockAdjustmentController;
use App\Http\Controllers\Api\StockMovementRequestController;
use App\Http\Controllers\Api\SubCategoryController;
use App\Http\Controllers\Api\TemplateController;
use App\Http\Controllers\Api\TypePeriodController;
use App\Http\Controllers\Api\WarehouseController;
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
        Route::prefix('pos-order')->group(function () {
            Route::get('/', [PosOrderController::class, 'index']);
            Route::post('/', [PosOrderController::class, 'store']);
            Route::get('/{id}', [PosOrderController::class, 'show']);
            Route::get('hold/list/{warehouseId}', [PosOrderController::class, 'heldOrders']);
            Route::post('{id}/cancel', [PosOrderController::class, 'cancel']);
            Route::post('{id}/complete', [PosOrderController::class, 'complete']);
            Route::post('{id}/hold', [PosOrderController::class, 'hold']);
            Route::post('{id}/resume', [PosOrderController::class, 'resume']);
        });
        Route::prefix('sales-order')->group(function () {
            Route::get('/', [SalesOrderController::class, 'index']);
            Route::post('/', [SalesOrderController::class, 'store']);
            Route::get('/{id}', [SalesOrderController::class, 'show']);
            Route::post('{id}/cancel', [SalesOrderController::class, 'cancel']);
            Route::post('{id}/complete', [SalesOrderController::class, 'complete']);
        });

        Route::prefix('orders-return')->group(function () {
            Route::get('/', [OrderReturnController::class, 'index']);
            Route::post('/', [OrderReturnController::class, 'store']);
            Route::get('/{id}', [OrderReturnController::class, 'show']);
            Route::post('/update/{id}', [OrderReturnController::class, 'update']);
            Route::delete('/{id}', [OrderReturnController::class, 'destroy']);
            Route::patch('/{id}/change-status', [OrderReturnController::class, 'changeStatus']);
            Route::post('/{id}/add-payment', [OrderReturnController::class, 'addPayment']);

            Route::get('{id}/restore', [OrderReturnController::class, 'restore']);
            Route::delete('{id}/force', [OrderReturnController::class, 'forceDestroy']);
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
        Route::prefix('inventroy')->group(function () {
            Route::get('/summary', [InventoryController::class, 'index']);
            Route::get('movements', [InventoryController::class, 'movements']);

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
            Route::patch('/{id}/toggle-status', [ResignationController::class, 'toggleStatus']);
        });
        //rejoins routes
        Route::prefix('rejoins')->group(function () {
            Route::get('/', [RejoinController::class, 'index']);
            Route::post('/', [RejoinController::class, 'store']);
            Route::get('/{id}', [RejoinController::class, 'show']);
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
        //change-pass routes
        Route::prefix('change-pass')->group(function () {
            Route::post('/', [PassChangeController::class, 'update']);
        });
        //market tools route
        Route::post('market-tools/update', [MarketController::class, 'update']);

    });
});
