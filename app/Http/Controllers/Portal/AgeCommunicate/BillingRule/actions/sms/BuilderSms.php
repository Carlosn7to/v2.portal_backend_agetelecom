<?php

namespace App\Http\Controllers\Portal\AgeCommunicate\BillingRule\actions\sms;

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

        $this->templates = new TemplatesSms();

        foreach($this->data as $key => $value){

            $template = $this->templates->getTemplate($value['segmentation'], $value['days_until_expiration']);

            if($template != null){

                $dataSending = [
                    'clientKey' => $key,
                    'template' => $template
                ];

                $this->chooseIntegrator($dataSending);

            }

        }



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


        $integrator = $dataSending['template']['integrator']['configuracao'];

        $configuration = new Configuration(
            host: $integrator['configuration']['host'],
            apiKey: $integrator['configuration']['apiKey']
        );

        $sendSmsApi = new SmsApi(config: $configuration);

        $message = new SmsTextualMessage(
            destinations: [
                new SmsDestination(to: '+'.'5561984700440')// $this->data[$dataSending['clientKey']]['phone'])
            ],
            from: 'InfoSMS',
            text: `${}`,
            notifyUrl: 'http://localhost:8000/infobip/report/sms/'
        );


        $request = new SmsAdvancedTextualRequest(messages: [$message]);

        try {
            $smsResponse = $sendSmsApi->sendSmsMessage($request);

            $this->smsReport();

            return;

        } catch (ApiException $apiException) {
            // HANDLE THE EXCEPTION
        }

    }

    private function buildingReport()
    {


    }

}
