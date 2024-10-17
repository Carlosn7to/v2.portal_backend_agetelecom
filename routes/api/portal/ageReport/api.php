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


Route::prefix('management')->group(function () {
    Route::prefix('reports')->controller(\App\Http\Controllers\Portal\AgeReport\Management\Reports\ReportsController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
    });

    Route::prefix('users')->controller(\App\Http\Controllers\Portal\AgeReport\Management\Users\UsersController::class)->group(function () {
        Route::get('/', 'getAllUsers');
        Route::get('/byName/{name}', 'getUserByName');

        Route::prefix('roles')->controller(\App\Http\Controllers\Portal\AgeReport\Management\Users\UsersRolesController::class)->group(function () {
            Route::post('/', 'defineUserRoles');
            Route::get('/reports', 'getReports');
        });

    });
});

Route::prefix('reports')->controller(\App\Http\Controllers\Portal\AgeReport\Reports\ReportsController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('/building', 'buildingReport');
    Route::get('/download/{assignmentId}', 'downloadReport');
    Route::get('/{id}', 'show');
    Route::get('/{id}/columns', 'getColumns');
});
