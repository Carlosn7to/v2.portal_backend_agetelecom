<?php

namespace App\Http\Controllers\Portal\AgeCommunicate\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Infobip\Model\SmsInboundMessageResult;
use Infobip\ObjectSerializer;
use Infobip\Model\SmsReport;

class RealTimeController extends Controller
{

    public function __construct()
    {
        $this->middleware('portal.ageCommunicate.infoBip.access');
    }

    public function handle(Request $request)
    {



        return response()->json(['message' => 'Webhook recebido com sucesso!'], 200);
    }
}
