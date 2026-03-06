<?php

use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductBatchController;
use App\Http\Controllers\Api\WarehouseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes



Route::apiResource('brands', BrandController::class);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('warehouses', WarehouseController::class);
Route::apiResource('product-batches', ProductBatchController::class);

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
