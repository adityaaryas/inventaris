<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\StockExistControler;
use App\Http\Controllers\StockEntryController;
use App\Http\Controllers\Api\CategoryController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        // Category routes
         Route::apiResource('categories', CategoryController::class);
         Route::get('categories/with-items', [CategoryController::class, 'withItems']);
         Route::get('categories/with-count', [CategoryController::class, 'withCount']);
        
        // Item routes
         Route::apiResource('items', ItemController::class)->except('show');
         Route::get('items/low-stock', [ItemController::class, 'lowStock']);
         Route::patch('items/{id}/stock', [ItemController::class, 'updateStock']); 

        Route::apiResource('stock-entries', StockEntryController::class);
        Route::apiResource('stock-exits', StockExistControler::class);
    });
});
