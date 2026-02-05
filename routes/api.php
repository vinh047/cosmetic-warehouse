<?php

use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
    Route::apiResource('brands', BrandController::class);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Lấy info user đang login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Products
    Route::apiResource('products', ProductController::class);

    // Brand
});
