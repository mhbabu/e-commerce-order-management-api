<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:api')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login'])->name('login');

    Route::middleware('auth:api')->group(function () {
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        // Index accessible by customer, vendor, admin
        Route::get('products', [ProductController::class, 'index'])->middleware('role:customer,vendor,admin');
        Route::get('products/search', [ProductController::class, 'search'])->middleware('role:customer,vendor,admin');

        // Other product routes restricted to vendor and admin
        Route::middleware('role:vendor,admin')->group(function () {
            Route::apiResource('products', ProductController::class)->except(['index']);
            Route::post('products/bulk-import', [ProductController::class, 'bulkImport']);
        });

        Route::middleware('role:customer,vendor,admin')->group(function () {
            Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show']);
            Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);
            Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
            Route::post('orders/{order}/generate-invoice', [OrderController::class, 'generateInvoice']);
            Route::get('orders/{order}/download-invoice', [OrderController::class, 'downloadInvoice']);
        });
    });
});
