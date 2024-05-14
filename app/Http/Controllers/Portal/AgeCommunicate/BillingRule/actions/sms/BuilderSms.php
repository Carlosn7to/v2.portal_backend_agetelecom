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

//////////// Função para debug de templates
        foreach($buildingSends as $key => &$value) {

            if(isset($value['clients'])) {

                $value['clients'] = array_slice($value['clients'], 0, 1);

                foreach($value['clients'] as $k => &$v) {

                    $v['phone'] = '5561984700440';

                }

            }

        }


        foreach($buildingSends as $key => &$value) {
            $valueFormmated = $this->getVariablesForTemplate($value);

            return $valueFormmated;
//            $this->chooseIntegrator($valueFormmated);
        }


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

    private function infoBip($buildingSends)
    {
        $integrator = $buildingSends['integrator']['configuracao']['configuration'];

        $destinations = [];

        if(isset($buildingSends['clients'])) {
            foreach($buildingSends['clients'] as $key => $value) {
                $destinations[] = ['to' => $value['phone']];
            }
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
                            'destinations' => [],
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

    private function getVariablesForTemplate($template)
    {
        dd($template);

    }
}
