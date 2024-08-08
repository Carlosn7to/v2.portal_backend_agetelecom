<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::prefix('capacity')->controller(\App\Http\Controllers\Integrator\Aniel\Schedule\BuilderController::class)->group(function () {
    Route::get('/', 'getCapacity');
    Route::get('/calendar', 'getCalendar');
    Route::get('/reschedule', 'capacityReschedule');
});

Route::prefix('management-schedule')->controller(\App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Management\DashboardSchedule::class)->group(function () {
    Route::get('/dashboard', 'getDashboard');
    Route::post('/approval-order', 'approvalOrder');
    Route::post('/reschedule-order', 'rescheduleOrder');
});

Route::prefix('management-schedule')->controller(\App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Management\DashboardSchedule::class)->group(function () {
    Route::get('/dashboard', 'getDashboard');
    Route::get('/dashboard-operational', 'getDashboardOperational');
    Route::post('/approval-order', 'approvalOrder');
    Route::post('/reschedule-order', 'rescheduleOrder');
});

Route::prefix('communicate-order')->controller(\App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Communicate\SendingController::class)->group(function () {
    Route::post('/status', 'updateStatusSending');
    Route::post('/send/confirm', 'sendUniqueConfirm');
});

Route::prefix('management-order')->controller(\App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Management\OrderActionsController::class)->group(function () {
    Route::get('/data', 'getDataOrder');
    Route::post('/send/confirm', 'sendConfirm');
    Route::post('/actions/reschedule', 'rescheduleOrder');
});

