<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::prefix('seller/dashboard')->controller(\App\Http\Controllers\Portal\AgeRv\B2b\Seller\Commission\BuilderController::class)->group(function () {
    Route::get('/', 'response');
});


Route::prefix('commission/financial/builder')->controller(\App\Http\Controllers\Portal\AgeRv\B2b\Commission\Financial\Provisory2BuilderController::class)->group(function () {
    Route::get('/', 'builder');
    Route::get('/seller', 'sellerData');
});

Route::prefix('commission/seller/dashboard')->controller(\App\Http\Controllers\Portal\AgeRv\B2b\Seller\Commission\BuilderController::class)
        ->group(function() {
    Route::get('/', 'response');
});
