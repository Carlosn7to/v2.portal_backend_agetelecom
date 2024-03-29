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

Route::prefix('auth')->controller(\App\Http\Controllers\AppClient\Auth\AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
    Route::post('me', 'me');
});


Route::middleware('appClient.auth')->prefix('/email-validate')->group(function () {
    Route::controller(\App\Http\Controllers\AppClient\ValidateEmail\ValidateEmailController::class)->group(function () {
       Route::post('send', 'send');
    });
});
