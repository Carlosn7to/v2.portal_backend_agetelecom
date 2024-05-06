<?php

namespace App\Http\Controllers\Portal\AgeCommunicate\BillingRule\actions\sms;

use App\Models\Portal\AgeCommunicate\BillingRule\Reports\ReportSms;
use GuzzleHttp\Client;
use Infobip\Api\SmsApi;
use Infobip\ApiException;
use Infobip\Configuration;
use Infobip\Model\SmsAdvancedTextualRequest;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsTextualMessage;
use Infobip\Model\SmsReportResponse;
use Infobip\ObjectSerializer;

class BuilderSms
{

    private $templates;
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function builder()
    {

        $this->templates = (new TemplatesSms())->getTemplates();

        $buildingSends = [];

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


        foreach($this->templates as $key => $value) {
            $buildingSends[] = $value;
        }


        foreach($buildingSends as $key => &$value) {

            foreach($this->data as $k => $v) {

                if(in_array($v['days_until_expiration'], $value['rule']['categorias'][$v['segmentation']])){
                    $buildingSends[$key]['clients'][] = $v;
                }
            }

        }


        foreach($buildingSends as $key => &$value) {

            if(isset($value['clients'])) {

                $value['clients'] = array_slice($value['clients'], 0, 2);

                foreach($value['clients'] as $k => &$v) {

                    $v['phone'] = '5561981069695';

                }

            }

        }

        foreach($buildingSends as $key => &$value) {
            $this->chooseIntegrator($value);
        }


//        return $this->buildingReport($buildingSends);


//
//        foreach($this->data as $key => $value){
//
//            $template = $this->templates->getTemplate($value['segmentation'], $value['days_until_expiration']);
//
//            if($template != null){
//
//                $dataSending = [
//                    'clientKey' => $key,
//                    'template' => $template
//                ];
//
//                $this->chooseIntegrator($dataSending);
//
//            }
//
//        }



    }

    private function chooseIntegrator($buildingSends)
    {

        switch(mb_convert_case($buildingSends['integrator']['titulo'], MB_CASE_LOWER, 'UTF-8')){

            case 'infobip':
                $this->infoBip($buildingSends);
                break;

            default:
                dd('Integrador não encontrado');
                break;

        }

    }

    public function smsReport()
    {


    }

    private function infoBip($buildingSends)
    {
        $integrator = $buildingSends['integrator']['configuracao']['configuration'];

        $destinations = [];

        foreach($buildingSends['clients'] as $key => $value) {
            $destinations[] = ['to' => $value['phone']];
        }

            // Configurar o cliente Guzzle
            $client = new Client([
                'base_uri' => $integrator['host'],
                'http_errors' => false, // Impedir que Guzzle gere exceções para códigos de erro HTTP
            ]);

            $response = $client->post('sms/2/text/advanced', [
                'headers' => [
                    'Authorization' => $integrator['apiKey'],
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'bulkId' => '',
                    'messages' => [
                        [
                            'destinations' => $destinations,
                            'from' => 'Age Telecom',
                            'text' => $buildingSends['content'],
                            'entityId' => 'portal_agetelecom_colaborador',
                            'applicationId' => 'portal_agetelecom_colaborador'
                        ],
                    ],
                ],
            ]);

        // Obter a resposta como JSON
        $responseData = json_decode($response->getBody(), true);

        $this->buildingReport($buildingSends, $responseData);

    }

    private function buildingReport($report, $response)
    {


        $reportSms = new ReportSms();

        if(isset($report['clients'])) {
            foreach($report['clients'] as $k => $v) {


                $reportStatus = $reportSms->create([
                    'bulk_id' => isset($response['bulkId']) ? $response['bulkId'] : 'envio_individual',
                    'mensagem_id' => $this->getMessageId($v['phone'], $response['messages']),
                    'canal' => 'SMS',
                    'contrato_id' => $v['contract_id'],
                    'fatura_id' => $v['frt_id'],
                    'celular' => $v['phone'],
                    'celular_voalle' => $v['phone_original'],
                    'segregacao' => $v['segmentation'],
                    'regra' => $v['days_until_expiration'],
                    'status' => 100,
                    'status_descricao' => 200,
                    'erro' => $v['phone'] != null ? null : '{"error": "O campo celular não está preenchido no voalle"}',
                    'template_sms_id' => $report['id_template']
                ]);


            }
        }

    }

    private function getMessageId($phone, $messages)
    {
        foreach($messages as $key => $value) {
            if($value['to'] == $phone) {
                return $value['messageId'];
            }
        }
    }
}
