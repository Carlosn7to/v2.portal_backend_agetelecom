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
        $this->buildingData();
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

    private function buildingData()
    {
        $this->getData();

        // Usando um array associativo para armazenar o contrato com o maior 'days_until_expiration' para cada 'contract_id'
        $contractById = [];

        // Percorrendo os contratos e mantendo apenas o com maior 'days_until_expiration' para cada 'contract_id'
        foreach ($this->data as $contract) {
            $contractId = $contract['contract_id'];

            // Se o 'contract_id' já existir, mantenha apenas aquele com o maior 'days_until_expiration'
            if (isset($contractById[$contractId])) {
                if ($contract['days_until_expiration'] > $contractById[$contractId]['days_until_expiration']) {
                    $contractById[$contractId] = $contract;
                }
            } else {
                // Se não, adicione ao array
                $contractById[$contractId] = $contract;
            }
        }

        // Convertendo de volta para um array indexado
        $this->data = array_values($contractById);
    }



}
