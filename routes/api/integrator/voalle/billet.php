<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::prefix('getBillet')->controller(\App\Http\Controllers\Integrator\Voalle\Billets\BilletController::class)->group(function () {
    Route::get('/{id}', 'getBillet');
});
