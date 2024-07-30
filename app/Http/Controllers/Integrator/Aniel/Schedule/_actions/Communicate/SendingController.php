<?php

namespace App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Communicate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SendingController extends Controller
{
    public function updateStatusSending(Request $request)
    {
        $info = new InfoOrder();

        return response()->json([$request->phone, $request->response]);
    }
}
