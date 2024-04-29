<?php

namespace App\Http\Controllers\Portal\AgeCommunicate\BillingRule;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\AgeCommunicate\BillingRule\actions\sms\BuilderSms;
use App\Models\Portal\AgeCommunicate\BillingRule\DataVoalle;
use Illuminate\Http\Request;

class BuilderBillingRuleController extends Controller
{
    private $data;

    public function builder()
    {
        $this->getData();
        return $this->sendingCommunication();
    }


    private function sendingCommunication()
    {

        $smsAction = (new BuilderSms($this->data))->builder();

        return $smsAction;

    }

    private function getData() : void
    {
        $this->data = (new DataVoalle())->getDataResults();


    }



}
