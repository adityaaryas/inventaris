<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\stockEntryController;
use App\Http\Controllers\stockExistControler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('items', ItemController::class);
        Route::apiResource('stock-entries', stockEntryController::class);
        Route::apiResource('stock-exits', stockExistControler::class);
    });
});
