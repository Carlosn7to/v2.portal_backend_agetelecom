<?php

namespace App\Http\Controllers\Portal\AgeCommunicate\BillingRule;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\AgeCommunicate\BillingRule\actions\sms\BuilderSms;
use Illuminate\Http\Request;

class BuilderBillingRuleController extends Controller
{
    public function builder()
    {
        $smsAction = (new BuilderSms('(61) 8470-0440', '10'))->builder();

        return $smsAction;


    }
}
