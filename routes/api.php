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

        // Other product routes restricted to vendor and admin
        Route::middleware('role:vendor,admin')->group(function () {
            Route::apiResource('products', ProductController::class)->except(['index']);
            Route::post('products/bulk-import', [ProductController::class, 'bulkImport']);
        });

        Route::middleware('role:customer,vendor,admin')->group(function () {
            Route::get('orders', [OrderController::class, 'index']);
            Route::post('orders', [OrderController::class, 'store']);
            Route::get('orders/{id}', [OrderController::class, 'show']);
            Route::patch('orders/{id}/status', [OrderController::class, 'updateStatus']);
            Route::post('orders/{id}/cancel', [OrderController::class, 'cancel']);
        });
    });
});
