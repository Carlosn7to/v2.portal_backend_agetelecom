<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::prefix('capacity')->controller(\App\Http\Controllers\Integrator\Aniel\Schedule\BuilderController::class)->group(function () {
    Route::get('/', 'getCapacity');
    Route::get('/calendar', 'getCalendar');
});
