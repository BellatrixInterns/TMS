<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UomController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

// Everything below needs a logged-in user (token).
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('categories', CategoryController::class)->except('show');
    Route::apiResource('brands', BrandController::class)->except('show');
    Route::apiResource('uoms', UomController::class)->except('show');
    Route::apiResource('locations', LocationController::class)->except('show');
    Route::apiResource('suppliers', SupplierController::class)->except('show');
    Route::apiResource('items', ItemController::class)->except('show');

    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index']);
    Route::post('/purchase-orders', [PurchaseOrderController::class, 'store']);
});