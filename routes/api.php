<?php

use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\InventoryTransactionController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductBatchController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\WarehouseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes



Route::apiResource('brands', BrandController::class);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('warehouses', WarehouseController::class);
Route::apiResource('product-batches', ProductBatchController::class);
Route::apiResource('inventory-transactions', InventoryTransactionController::class);
Route::apiResource('stocks', StockController::class);

Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/', [OrderController::class, 'store']);
    Route::get('/{order}', [OrderController::class, 'show']);

    // Đích đến của chúng ta đây: Dùng PATCH để cập nhật trạng thái
    Route::patch('/{order}/status', [OrderController::class, 'updateStatus']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Lấy info user đang login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Product

    // Brand

    // Category

    // Warehouse

    // ProductBatch

});
