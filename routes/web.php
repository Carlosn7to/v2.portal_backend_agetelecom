<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/order/{id}', [\App\Http\Controllers\Integrator\Aniel\Services\Orders\OrderController::class, 'getOrder']);
Route::get('/order', [\App\Http\Controllers\Integrator\Aniel\Services\Orders\OrderController::class, 'store']);
Route::get('/order/edit/{id}', [\App\Http\Controllers\Integrator\Aniel\Services\Orders\OrderController::class, 'edit']);




Route::get('bi/voalle/financial/b2b/good-payer', [\App\Http\Controllers\Portal\BI\Voalle\Financial\B2B\GoodPayerController::class, 'builderForBI']);
Route::get('bi/voalle/financial/b2b/receipt-titles', [\App\Http\Controllers\Portal\BI\Voalle\Financial\B2B\ReceipTitlesController::class, 'builderForBI']);


Route::get('teste-email', function () {
    return view('portal.mail.collaborators');
});
