<?php

namespace App\Http\Controllers\Portal\AgeCommunicate\BillingRule;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\AgeCommunicate\BillingRule\actions\mail\BuilderEmail;
use App\Http\Controllers\Portal\AgeCommunicate\BillingRule\actions\sms\BuilderSms;
use App\Http\Controllers\Portal\AgeCommunicate\BillingRule\actions\whatsapp\BuilderWhatsapp;
use App\Jobs\SendEmailMessage;
use App\Jobs\SendSmsMessage;
use App\Jobs\SendWhatsappMessage;
use App\Models\Portal\AgeCommunicate\BillingRule\DataVoalle;
use App\Models\Portal\AgeCommunicate\BillingRule\Reports\Report;
use Carbon\Carbon;
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
        set_time_limit(2000000000);
        $this->buildingData();
        return $this->sendingCommunication();
    }

    public function debug()
    {
        $this->buildingData();
        $this->sendingCommunication();

    }

    private function sendingCommunication()
    {
        $timer = 60*1;

        $whatsappAction = new BuilderWhatsapp($this->data);
        $smsAction = new BuilderSms($this->data);
        $emailAction = new BuilderEmail($this->data);
        $this->sendAlert(($timer / 60), $whatsappAction->infoSending(), $smsAction->infoSending(), $emailAction->infoSending());
        sleep($timer);
        SendWhatsappMessage::dispatch($this->data);
        SendSmsMessage::dispatch($this->data);
        SendEmailMessage::dispatch($this->data);
        return true;

    }

    public function sendAlert($timer, $whatsappInfo, $smsInfo, $emailInfo)
    {
        $authorization = 'App b13815e2d434d294b446420e41d4f4e6-6c3b9fe0-a751-45d5-aba0-7afbe9fb28bd';
        $client = new Client();

        $destinations = [
            '5561984700440',
//            '5561981069695',
//            '5561998003186',
//            '5561992587560'
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
                                            "$timer",
                                            "$whatsappInfo envios, total: R$ ". number_format(($whatsappInfo * .26), 2, ',', '.'),
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


        $dataSendedsToday = Report::where('created_at', '>=', Carbon::now()->startOfDay()->format('Y-m-d H:i:s'))->get();

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


        foreach($this->data as $key => &$value) {
            foreach($dataSendedsToday as $k => $v) {
                if($value['contract_id'] == $v['contrato_id']) {
                    unset($this->data[$key]);
                }
            }
        }

    }



}
