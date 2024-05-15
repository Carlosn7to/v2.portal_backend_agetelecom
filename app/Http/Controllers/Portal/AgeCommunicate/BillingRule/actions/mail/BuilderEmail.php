<?php

namespace App\Http\Controllers\Portal\AgeCommunicate\BillingRule\actions\mail;

use App\Http\Controllers\Portal\AgeCommunicate\BillingRule\actions\sms\TemplatesSms;
use App\Mail\Portal\AgeCommunicate\Rule\Billing\SendBilling;
use App\Models\Portal\AgeCommunicate\BillingRule\Reports\Report;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;
use GuzzleHttp\Client;
use Infobip\Api\SmsApi;
use Infobip\ApiException;
use Infobip\Configuration;
use Infobip\Model\SmsAdvancedTextualRequest;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsTextualMessage;
use Infobip\Model\SmsReportResponse;
use Infobip\ObjectSerializer;

class BuilderEmail
{

    private $templates;
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
        $this->authenticateVoalle();
    }

    public function builder()
    {
        $this->templates = (new TemplatesEmail())->getTemplates();

        $buildingSends = [];

        foreach($this->data as $key => &$value) {

                foreach($this->templates as $k => $v) {

                    if(in_array($value['days_until_expiration'], $v['rule']['categorias'][$value['segmentation']])){
                        $buildingSends[$key] = $value;
                        $buildingSends[$key]['template'] = $v;
                    }
                }
        }

////////// Função para debug de templates
//        foreach($buildingSends as $key => &$value) {
//
//            $value['email'] = 'carlos.neto@agetelecom.com.br';
//        }


        foreach($buildingSends as $key => &$value) {
            try {
                $this->chooseIntegrator($value);
            } catch (\Exception $e) {
                // Registra o erro para depuração
                \Log::error('Erro ao escolher integrador', [
                    'error' => $e->getMessage(),
                    'value' => $value
                ]);
            }
        }


    }

    private function chooseIntegrator($clientData)
    {

        switch(mb_convert_case($clientData['template']['integrator']['titulo'], MB_CASE_LOWER, 'UTF-8')){

            case 'aws':
                $this->aws($clientData);
                break;

            default:
                dd('Integrador não encontrado');
                break;

        }

    }

    private function aws($clientData)
    {
        $client = new Client();

        $responseBillet = $client->get('https://erp.agetelecom.com.br:45715/external/integrations/thirdparty/GetBillet/'.$clientData['frt_id'],[
            'headers' => [
                'Authorization' => 'Bearer '.$this->access->access_token
            ]
        ]);

        $billetPath = [];

        // Verifique se a requisição foi bem-sucedida (código de status 200)
        if ($responseBillet->getStatusCode() == 200) {
            // Obtenha o conteúdo do PDF
            $pdfContent = $responseBillet->getBody()->getContents();

            // Especifique o caminho onde você deseja salvar o arquivo no seu computador
            $billetPath = storage_path('app/portal/agecommunicate/billingrule/billets/boleto.pdf');

            // Salve o arquivo no caminho especificado
            file_put_contents($billetPath, $pdfContent);


        }

        if($clientData['pix_qrcode'] != null){
            $result = Builder::create()
                ->writer(new PngWriter())
                ->writerOptions([])
                ->data($clientData['pix_qrcode'])
                ->encoding(new Encoding('UTF-8'))
                ->size(300)
                ->margin(10)
                ->build();

            $qrCode = $result->getString();

            $clientData['pix_qrcode'] = $qrCode;
        }


        try {
            if (filter_var($clientData['email'], FILTER_VALIDATE_EMAIL)) {
                // Tente enviar o e-mail
                \Mail::mailer('fat')->to($clientData['email'])
                    ->send(new SendBilling(
                        $clientData['template']['template_integrator'],
                        $clientData['template']['title'],
                        $clientData,
                        $billetPath
                    ));
            } else {
                // Caso o e-mail seja inválido, registre um erro
                $clientData['error'] = json_encode(['error' => 'E-mail inválido para disparo']);
                \Log::error('E-mail inválido', ['clientData' => $clientData]);
            }
        } catch (\Exception $e) {
            // Capture qualquer exceção e registre o erro
            $clientData['error'] = json_encode(['error' => $e->getMessage()]);
            \Log::error('Erro ao enviar e-mail', ['error' => $e->getMessage(), 'clientData' => $clientData]);
        }

//        $this->buildingReport($clientData);

    }

    private function buildingReport($clientData)
    {
        $report = new Report();

        $reportStatus = $report->create([
            'bulk_id' => '',
            'mensagem_id' => uniqid() . "_" . $clientData['contract_id'],
            'canal' => 'Email',
            'contrato_id' => $clientData['contract_id'],
            'fatura_id' => $clientData['frt_id'],
            'celular' => $clientData['phone'],
            'celular_voalle' => $clientData['phone_original'],
            'email' => $clientData['email'],
            'segregacao' => $clientData['segmentation'],
            'regra' => $clientData['days_until_expiration'],
            'status' => 100,
            'status_descricao' => 201,
            'erro' => $clientData['error'] ?? 'null',
            'template_id' => $clientData['template']['id_template']
        ]);

    }

    private function authenticateVoalle()
    {
        $client = new Client();

        $dataForm = [
            "grant_type" => "client_credentials",
            "scope" => "syngw",
            "client_id" => config('services.voalle.client_id'),
            "client_secret" => config('services.voalle.client_secret'),
            "syndata" => config('services.voalle.syndata')
        ];

        $response = $client->post('https://erp.agetelecom.com.br:45700/connect/token', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => $dataForm
        ]);

        $this->access = json_decode($response->getBody()->getContents());

    }

    public function infoSending()
    {
        $this->templates = (new TemplatesEmail())->getTemplates();

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
