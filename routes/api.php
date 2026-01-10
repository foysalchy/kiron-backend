<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\PartyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {

    Route::get('/ping', function () {
        return 'pong';
    });
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

        // company routes
            Route::prefix('companies')->group(function () {
            Route::get('/search/query', [CompanyController::class, 'search']);
            Route::get('/active/list', [CompanyController::class, 'getActiveCompanies']);
            Route::get('/', [CompanyController::class, 'index']);
            Route::post('/', [CompanyController::class, 'store']);
            Route::get('/{id}', [CompanyController::class, 'show']);
            Route::post('/update/{id}', [CompanyController::class, 'update']);
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
            Route::patch('/{id}', [PartyController::class, 'update']);
            Route::delete('/{id}', [PartyController::class, 'destroy']);
            Route::get('/{id}/restore', [PartyController::class, 'restore']);
            Route::delete('/{id}/force', [PartyController::class, 'forceDestroy']);
            Route::patch('/{id}/toggle-status', [PartyController::class, 'toggleStatus']);
            Route::patch('/{id}/update-balance', [PartyController::class, 'updateBalance']);
        });
    });
});
