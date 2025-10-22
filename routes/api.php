<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\SsoController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\PharmacyController;
use App\Http\Controllers\Api\V1\MedicationController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\CustomerAuthController;
use App\Http\Controllers\Api\V1\PharmacyProductController;
use App\Http\Controllers\Api\V1\StorefrontSettingsController;

// Prefix all routes with v1
Route::prefix('v1')->group(function () {

    // --- PUBLIC ROUTES ---
    Route::apiResource('pharmacies', PharmacyController::class)->only(['show']);
    Route::apiResource('medications', MedicationController::class)->only(['index', 'show']);
    Route::apiResource('pharmacy-products', PharmacyProductController::class)->only(['index', 'show']);

    // --- CUSTOMER AUTH ROUTES ---
    Route::post('/customer/register', [CustomerAuthController::class, 'register']);
    Route::post('/customer/login', [CustomerAuthController::class, 'login']);

    Route::apiResource('categories', CategoryController::class)->only(['index']);

    // --- PROTECTED ROUTES (Requires Sanctum Token) ---
    Route::middleware('auth:sanctum')->group(function () {
        // Customer-specific routes
        Route::get('/customer/profile', [CustomerController::class, 'getProfile']);
        Route::put('/customer/profile', [CustomerController::class, 'updateProfile']);
        Route::get('/customer/orders', [CustomerController::class, 'getOrders']);
        Route::post('/customer/logout', [CustomerAuthController::class, 'logout']);

        // Storefront settings endpoint
        Route::get('/storefront/settings', StorefrontSettingsController::class);

        // Order creation for storefronts
        Route::apiResource('orders', OrderController::class)->only(['store']);

        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
        Route::post('/transactions/{transaction:reference}/verify', [TransactionController::class, 'verify'])->name('transactions.verify');

        Route::post('/auth/sso-token', [SsoController::class, 'generateToken']);
    });
});
