<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\InventoryTransactionController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductBatchController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WarehouseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);



// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Brand
    Route::post('brands/{brand}/restore', [BrandController::class, 'restore'])->withTrashed();
    Route::apiResource('brands', BrandController::class);

    // Category
    Route::post('categories/{category}/restore', [CategoryController::class, 'restore'])->withTrashed();
    Route::apiResource('categories', CategoryController::class);

    // Warehouse
    Route::post('warehouses/{warehouse}/restore', [WarehouseController::class, 'restore'])
        ->withTrashed();
    Route::apiResource('warehouses', WarehouseController::class);

    // Product
    Route::post('products/{product}/restore', [ProductController::class, 'restore'])
        ->withTrashed();
    Route::apiResource('products', ProductController::class);

    // ProductBatch
    Route::post('product-batches/{product_batch}/restore', [ProductBatchController::class, 'restore'])
        ->withTrashed();
    Route::apiResource('product-batches', ProductBatchController::class);

    // inventory-transaction
    Route::apiResource('inventory-transactions', InventoryTransactionController::class)
        ->except(['update', 'destroy']);

    // stock
    Route::apiResource('stocks', StockController::class)->only(['index', 'show']);

    // order
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::apiResource('orders', OrderController::class)->except(['update', 'destroy']);

    // notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // User
    Route::post('users/{id}/restore', [UserController::class, 'restore']);
    Route::apiResource('users', UserController::class);
});
