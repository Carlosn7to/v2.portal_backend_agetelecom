<?php

namespace App\Http\Controllers\Portal\AgeCommunicate\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Infobip\Model\SmsInboundMessageResult;
use Infobip\ObjectSerializer;
use Infobip\Model\SmsReport;

class RealTimeController extends Controller
{
    public function handle(Request $request)
    {
        \Log::info('Webhook recebido:', $request->all());


        return response()->json(['message' => 'Webhook recebido com sucesso!'], 200);
    }
}
