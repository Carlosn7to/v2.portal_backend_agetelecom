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


Route::prefix('test')->controller(\App\Http\Controllers\HealthChecker\TestController::class)->group(function () {
    Route::post('/', 'index');
});

Route::prefix('resources')->controller(\App\Http\Controllers\HealthChecker\ResourceServer::class)->group(function () {
    Route::get('/disk/space-available', 'getSpaceDiskAvailable');
});

Route::prefix('statistics')->controller(\App\Http\Controllers\HealthChecker\BuilderController::class)->group(function () {
    Route::post('/', 'storeStatistics');
    Route::get('/analytic', 'getAnalyticStatistics');
    Route::get('/status', 'getStatus');
    Route::get('/events', 'getLastEvents');
    Route::get('/latency', 'getLatency');
    Route::get('/queues', 'getQueues');
});
