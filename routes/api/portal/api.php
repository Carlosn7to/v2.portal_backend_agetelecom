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


Route::prefix('test')->controller(\App\Http\Controllers\Test\Portal\Functions::class)->group(function () {
    Route::post('/', 'index');
});

Route::post('test/event', function (Request $request) {
    $channelName = $request->post('channelName');
    $message = $request->post('message');


    broadcast(new \App\Events\PublicMessageEvent( $channelName, $message ));
});

Route::post('infobip/report/sms', [\App\Http\Controllers\Portal\AgeCommunicate\Reports\RealTimeController::class, 'handle']);

