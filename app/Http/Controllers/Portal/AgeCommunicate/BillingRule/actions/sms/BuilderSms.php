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
        $this->bulkId = uniqid() . "_" . date("Y-m-d_H:i:s");
    }

    public function builder()
    {
        $this->templates = (new TemplatesSms())->getTemplates();

        $buildingSends = [];

        foreach($this->data as $key => &$value) {

                foreach($this->templates as $k => $v) {

                    if(in_array($value['days_until_expiration'], $v['rule']['categorias'][$value['segmentation']])){
                        $buildingSends[$key] = $value;
                        $buildingSends[$key]['template'] = $v;
                    }
                }
        }

////////////// Função para debug de templates
//        foreach($buildingSends as $key => &$value) {
//
//            $value['phone'] = '5561984700440';
//        }

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


            $response = $client->post('sms/2/text/advanced', [
                'headers' => [
                    'Authorization' => $integrator['apiKey'],
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'bulkId' => $this->bulkId,
                    'messages' => [
                        [
                            'destinations' => ['to'=> $clientData['phone']],
                            'from' => 'Age Telecom',
                            'text' => $clientData['template']['content'],
                            'entityId' => 'portal_agetelecom_colaborador',
                            'applicationId' => 'portal_agetelecom_colaborador'
                        ],
                    ],
                ],
            ]);


        // Obter a resposta como JSON
        $responseData = json_decode($response->getBody(), true);

        $this->buildingReport($clientData, $responseData);

    }

    private function buildingReport($clientData, $response)
    {
        $reportSms = new ReportSms();

        $reportStatus = $reportSms->create([
            'bulk_id' => isset($response['bulkId']) ? $response['bulkId'] : 'envio_individual',
            'mensagem_id' => $response['messages'][0]['messageId'],
            'canal' => 'SMS',
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
    }

    private function replaceVariablesForTemplate($clientData)
    {
        $clientData['template']['content'] = str_replace(
            [
                '{nome_cliente}',
                '{primeiro_nome_cliente}',
                '{dias_fatura}',
                '{codigo_barras}',
//                '{pix_qrcode}',
//                '{pix_copia_cola}',
                ],
            [
                $clientData['name'],
                explode(' ', $clientData['name'])[0],
                $clientData['days_until_expiration'],
                $clientData['barcode'],
//                $clientData['pix_qrcode'],
//                $clientData['pix_copia_cola'],
            ],
            $clientData['template']['content']
        );

        return $clientData;
    }


    public function infoSending()
    {
        $this->templates = (new TemplatesSms())->getTemplates();

        $buildingSends = [];

        foreach($this->data as $key => &$value) {

            foreach($this->templates as $k => $v) {

                if(in_array($value['days_until_expiration'], $v['rule']['categorias'][$value['segmentation']])){
                    $buildingSends[$key] = $value;
                    $buildingSends[$key]['template'] = $v;
                }
            }
        }

        return count($buildingSends);
    }
}
