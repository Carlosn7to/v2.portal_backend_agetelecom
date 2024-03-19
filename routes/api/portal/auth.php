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

Route::group([
], function ($router) {

    Route::post('logout', 'App\Http\Controllers\Portal\Auth\AuthController@logout');
    Route::post('refresh', 'App\Http\Controllers\Portal\Auth\AuthController@refresh');
    Route::post('me', 'App\Http\Controllers\Portal\Auth\AuthController@me');
    Route::post('login', 'App\Http\Controllers\Portal\Auth\AuthController@ldapAdOld');


});

