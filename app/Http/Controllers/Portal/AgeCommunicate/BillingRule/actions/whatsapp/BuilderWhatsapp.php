<?php

namespace App\Http\Controllers\Portal\AgeCommunicate\BillingRule\actions\whatsapp;

use App\Http\Controllers\Portal\AgeCommunicate\BillingRule\actions\sms\TemplatesSms;
use App\Models\Portal\AgeCommunicate\BillingRule\Reports\Report;
use GuzzleHttp\Client;
use Infobip\Api\SmsApi;
use Infobip\ApiException;
use Infobip\Configuration;
use Infobip\Model\SmsAdvancedTextualRequest;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsTextualMessage;
use Infobip\Model\SmsReportResponse;
use Infobip\ObjectSerializer;

class BuilderWhatsapp
{

    private $templates;
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
        $this->bulkId = uniqid() . "_" . date("Y-m-d_H:i:s");
    }

    public function builder()
    {
        $this->templates = (new TemplatesWhatsapp())->getTemplates();

        $buildingSends = [];


        foreach($this->data as $key => &$value) {

                foreach($this->templates as $k => $v) {

                    if(in_array($value['days_until_expiration'], $v['rule']['categorias'][$value['segmentation']])){
                        $buildingSends[$key] = $value;
                        $buildingSends[$key]['template'] = $v;
                    }
                }
        }



        foreach($buildingSends as $key => &$value) {
            $clientData = $this->replaceVariablesForTemplate($value);
            $this->chooseIntegrator($clientData);
        }


    }

    private function chooseIntegrator($clientData)
    {

        switch(mb_convert_case($clientData['template']['integrator']['titulo'], MB_CASE_LOWER, 'UTF-8')){

            case 'infobip':
                $this->infoBip($clientData);
                break;

            default:
                dd('Integrador não encontrado');
                break;

        }

    }

    private function infoBip($clientData)
    {
        $integrator = $clientData['template']['integrator']['configuracao']['configuration'];

        // Configurar o cliente Guzzle
        $client = new Client([
            'base_uri' => $integrator['host'],
            'http_errors' => false, // Impedir que Guzzle gere exceções para códigos de erro HTTP
        ]);

        $response = $client->post( 'whatsapp/1/message/template', [
            'headers' => [
                'Authorization' => $integrator['apiKey'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
                'bulkId' => $this->bulkId,
                'messages' => [
                    [
                        'from' => '556140404040',
                        'to' => $clientData['phone'],
                        'messageId' => '',
                        'content' => [
                            'templateName' => $clientData['template']['template_integrator'],
                            'templateData' => [
                                'body' => [
                                    'placeholders' => $clientData['variables_whatsapp'] ?? []
                                ],
                                'buttons' => [
                                    ['type' => 'QUICK_REPLY','parameter' => 'REALIZAR PAGAMENTO'],
                                    ['type' => 'QUICK_REPLY','parameter' => 'PAGAMENTO REALIZADO']
                                ]
                            ],
                            'language' => 'pt_BR'
                        ],
                        'entityId' => 'portal_agetelecom_colaborador',
                        'applicationId' => 'portal_agetelecom_colaborador',
                        'callbackData' => $clientData['contract_id'],
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

        // Obter a resposta como JSON
        $responseData = json_decode($response->getBody(), true);


        $this->buildingReport($clientData, $responseData);

    }

    private function buildingReport($clientData, $response)
    {
        $reportSms = new Report();

        $reportStatus = $reportSms->create([
            'bulk_id' => isset($response['bulkId']) ? $response['bulkId'] : 'envio_individual',
            'mensagem_id' => $response['messages'][0]['messageId'],
            'canal' => 'Whatsapp',
            'contrato_id' => $clientData['contract_id'],
            'fatura_id' => $clientData['frt_id'],
            'celular' => $clientData['phone'],
            'celular_voalle' => $clientData['phone_original'],
            'segregacao' => $clientData['segmentation'],
            'regra' => $clientData['days_until_expiration'],
            'status' => 100,
            'status_descricao' => 200,
            'erro' => $clientData['phone'] != null ? null : '{"error": "O campo celular não está preenchido no voalle"}',
            'template_id' => $clientData['template']['id_template']
        ]);

    }

    private function getMessageId($phone, $messages)
    {
        foreach($messages as $key => $value) {
            if($value['to'] == $phone) {
                return $value['messageId'];
            }
        }
        return null;
    }

    private function replaceVariablesForTemplate($clientData)
    {
        $variables = $clientData['template']['variables'];

        if($variables != null) {

            $clientData['variables_whatsapp'] = null;

            foreach($variables as $key => $value) {

                if($key == 'dias_fatura') {
                    $clientData['variables_whatsapp'][] = $clientData['days_until_expiration'];
                }

                if($key == 'nome_cliente') {
                    $clientData['variables_whatsapp'][] = $clientData['name'];
                }

                if($key == 'primeiro_nome_cliente') {
                    $clientData['variables_whatsapp'][] = explode(' ', $clientData['name'])[0];
                }

            }
        }
        return $clientData;
    }


    public function infoSending()
    {
        $this->templates = (new TemplatesWhatsapp())->getTemplates();

        $count = 0;

        $templates = [];


        foreach($this->templates as $key => $value) {
            $templates[$key] = [
                'titulo' => $value['title'],
                'template' => $value['template_integrator'],
                'categorias' => []
            ];

            foreach($value['rule']['categorias'] as $k => $v) {


                foreach($v as $kk => $aging) {
                    $templates[$key]['categorias'][] = [
                        'segmentacao' => $k,
                        'aging' => $aging,
                        'total' => 0
                    ];
                }

            }
        }


        foreach($this->data as $key => &$value) {


            foreach($templates as $k => &$v) {

                foreach($v['categorias'] as $kk => &$vv) {

                    if($value['days_until_expiration'] == $vv['aging'] && $value['segmentation'] == $vv['segmentacao']) {
                        $templates[$k]['categorias'][$kk]['total']++;
                        $count++;
                    }


                }
            }
        }

        $report = [];


        foreach($templates as $key => &$value) {


            if(isset($value['categorias'])) {
                foreach($value['categorias'] as $k => $v) {
                    if($v['total'] > 0) {
                        $report[] = [
                            'titulo' => $value['titulo'],
                            'segmentacao' => $v['segmentacao'],
                            'aging' => $v['aging'],
                            'total' => $v['total']
                        ];

                    }
                }
            }

        }

        return $count;
    }
}
