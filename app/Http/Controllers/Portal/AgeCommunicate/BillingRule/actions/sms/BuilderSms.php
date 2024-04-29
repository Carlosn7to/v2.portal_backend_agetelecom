<?php

namespace App\Http\Controllers\Portal\AgeCommunicate\BillingRule\actions\sms;

use App\Models\Portal\AgeCommunicate\BillingRule\Reports\ReportSms;
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



        return $this->buildingReport($buildingSends);




        return $buildingSends;
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

    private function chooseIntegrator($dataSending)
    {

        switch($dataSending['template']['integrator']['titulo']){

            case 'InfoBip':
                $this->infoBip($dataSending);
                break;

            default:
                dd('Integrador não encontrado');
                break;

        }

    }

    public function smsReport()
    {


    }

    private function infoBip($dataSending)
    {

        // Configurar o cliente Guzzle
        $client = new Client([
            'base_uri' => 'https://j36lvj.api-us.infobip.com/',
            'timeout' => 10.0, // Configuração do tempo limite
            'http_errors' => false, // Impedir que Guzzle gere exceções para códigos de erro HTTP
        ]);


        $integrator = $dataSending['template']['integrator']['configuracao'];
        $template = $dataSending['template'];
        $clientPhone =

        $response = $client->post('sms/2/text/advanced', [
            'headers' => [
                'Authorization' => 'App ' . $integrator['apiKey'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
                'bulkId' => 'Confirmação SMS 1',
                'messages' => [
                    [
                        'destinations' => [
                            ['to' => '+5561984700440'],
                        ],
                        'from' => 'Age Telecom',
                        'text' => 'Teste age - infoBip' . Carbon::now()->format('d/m/Y H:i:s'),
                    ],
                ],
            ],
        ]);

        // Obter a resposta como JSON
        $responseData = json_decode($response->getBody(), true);

        return response()->json([
            'status' => $response->getStatusCode(),
            'data' => $responseData,
        ]);

    }

    private function buildingReport($report)
    {
        $reportSms = new ReportSms();

        foreach($report as $key => $value){

            if(isset($value['clients'])) {
                foreach($value['clients'] as $k => $v) {

                    $reportStatus = $reportSms->create([
                        'bulk_id' => 'bulk_test',
                        'mensagem_id' => 'msg_test',
                        'contrato_id' => $v['contract_id'],
                        'fatura_id' => $v['frt_id'],
                        'celular' => $v['phone'],
                        'celular_voalle' => $v['phone_original'],
                        'segregacao' => $v['segmentation'],
                        'regra' => $v['days_until_expiration'],
                        'status' => $v['phone'] != null ? 'enviado' : 'erro',
                        'erro' => $v['phone'] != null ? null : '{"error": "O campo celular não está preenchido no voalle"}',
                        'template_id' => $value['id_template']
                    ]);


                }
            }

        }

    }

}
