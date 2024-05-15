<?php

namespace App\Http\Controllers\Portal\AgeCommunicate\BillingRule;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\AgeCommunicate\BillingRule\actions\mail\BuilderEmail;
use App\Http\Controllers\Portal\AgeCommunicate\BillingRule\actions\sms\BuilderSms;
use App\Http\Controllers\Portal\AgeCommunicate\BillingRule\actions\whatsapp\BuilderWhatsapp;
use App\Models\Portal\AgeCommunicate\BillingRule\DataVoalle;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Nette\Utils\Random;

class BuilderBillingRuleController extends Controller
{
    private $data;

    public function __invoke()
    {
        return $this->builder();
    }

    public function builder()
    {
        $this->buildingData();
        return $this->sendingCommunication();
    }


    private function sendingCommunication()
    {
//        $smsAction = new BuilderSms($this->data);
        $emailAction = new BuilderEmail($this->data);

        //        $whatsappAction = new BuilderWhatsapp($this->data);
//        return $whatsappAction->builder();

        $this->sendAlert(0, 10863, $emailAction->infoSending());
//        sleep(15*60);
        $emailAction->builder();
//        $smsAction->builder();
        return true;

    }

    public function sendAlert($whatsappInfo, $smsInfo, $emailInfo)
    {
        $authorization = 'App b13815e2d434d294b446420e41d4f4e6-6c3b9fe0-a751-45d5-aba0-7afbe9fb28bd';
        $client = new Client();

        $destinations = [
            '5561984700440',
            '5561981069695',
            '5511983705020',
            '5561998003186'
        ];


        foreach($destinations as $key => $destination) {
            $response = $client->request('POST', 'https://j36lvj.api-us.infobip.com/whatsapp/1/message/template', [
                'headers' => [
                    'Authorization' => $authorization, // Substitua {authorization} pelo token de autenticação real
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => [
                    'messages' => [
                        [
                            'from' => '556140404070',
                            'to' => $destination,
                            'messageId' => Random::generate(24),
                            'content' => [
                                'templateName' => 'regua_cobranca_aviso',
                                'templateData' => [
                                    'body' => [
                                        'placeholders' => [
                                            "$whatsappInfo envios, total: R$ ". number_format(($whatsappInfo * .4), 2, ',', '.'),
                                            "$smsInfo envios, total: R$ ". number_format(($smsInfo * .07), 2, ',', '.'),
                                            "$emailInfo envios, total: R$ ". number_format(($emailInfo * 0), 2, ',', '.'),
                                        ]
                                    ]
                                ],
                                'language' => 'pt_BR'
                            ],
                            'entityId' => 'portal_agetelecom_colaborador',
                            'applicationId' => 'portal_agetelecom_colaborador',
                            'callbackData' => 'Teste callback',
                        ]
                    ]
                ],
                'timeout' => 0,
                'allow_redirects' => [
                    'max' => 10,
                    'strict' => true,
                    'referer' => true,
                    'protocols' => ['https', 'http']
                ],
                'http_errors' => false
            ]);

        }


        return true;
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
