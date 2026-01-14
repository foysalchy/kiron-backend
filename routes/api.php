<?php

use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\AttributeGroupController;
use App\Http\Controllers\Api\AttributeValueController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CellController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ExtraCategoryController;
use App\Http\Controllers\Api\LogActionController;
use App\Http\Controllers\Api\MegaCategoryController;
use App\Http\Controllers\Api\MiniCategoryController;
use App\Http\Controllers\Api\PartyController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RackController;
use App\Http\Controllers\Api\SubCategoryController;
use App\Http\Controllers\Api\WarehouseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {

    Route::get('/ping', function () {
        return 'pong';
    });
    
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);

    Route::middleware('auth:sanctum','company.access')->group(function () {
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
            Route::get('/', [PartyController::class, 'index']);
            Route::post('/', [PartyController::class, 'store']);
            Route::get('/{id}', [PartyController::class, 'show']);
            Route::post('/update/{id}', [PartyController::class, 'update']);
            Route::delete('/{id}', [PartyController::class, 'destroy']);
            Route::get('/{id}/restore', [PartyController::class, 'restore']);
            Route::delete('/{id}/force', [PartyController::class, 'forceDestroy']);
            Route::patch('/{id}/toggle-status', [PartyController::class, 'toggleStatus']);
            Route::patch('/{id}/update-balance', [PartyController::class, 'updateBalance']);
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
        });
    });
});

