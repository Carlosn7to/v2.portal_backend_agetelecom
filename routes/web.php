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


Route::prefix('services')->group(function () {
    Route::prefix('blocked-screen')->group(function () {
        Route::get('/', [\App\Http\Controllers\Portal\Services\BlockedScreen\BuilderController::class, 'builder']);
    });
});


Route::get('rpa/contract/{contractId}', function ($contractId) {
    if(Request::get('token') != '0af09f37219dbf5fdaea13627ec5acee01669f6e99b202b2deee1f34a90ba6b6') {
        return response()->json(['Unauthorized'], 403);
    }
    $contractStatus = DB::connection('voalle')->select('select c.v_status as status from erp.contracts c where c.id = :contractId', ['contractId' => $contractId]);
    return response()->json($contractStatus);
});

