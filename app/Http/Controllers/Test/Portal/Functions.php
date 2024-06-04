<?php

namespace App\Http\Controllers\Test\Portal;

use App\Helpers\Portal\Mail\Notification\Builder;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\AgeCommunicate\BillingRule\actions\sms\TemplatesSms;
use App\Http\Controllers\Portal\AgeCommunicate\BillingRule\BuilderBillingRuleController;
use App\Http\Controllers\Portal\BI\Voalle\Financial\B2B\GoodPayerController;
use App\Http\Controllers\Portal\Management\User\UserController;
use App\Mail\Portal\AgeCommunicate\Rule\Billing\SendBilling;
use App\Models\Portal\AgeCommunicate\BillingRule\Reports\Report;
use App\Models\Portal\AgeCommunicate\BillingRule\Reports\ReportLog;
use App\Models\Portal\AgeCommunicate\BillingRule\Templates\Template;
use App\Models\Portal\User\User;
use App\Routines\Portal\Users\UserSync;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Infobip\Api\SmsApi;
use Infobip\Api\WhatsAppApi;
use Infobip\Configuration;
use Infobip\ApiException;
use Infobip\Model\SmsAdvancedTextualRequest;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsTextualMessage;
use Infobip\Model\WhatsAppBulkMessage;
use Illuminate\Http\Request;
use Nette\Utils\Random;

class Functions extends Controller
{

    private $user;

    public function __construct()
    {
//        $this->middleware('portal.ageCommunicate.infoBip.access')->only('index');
    }

    private function testSendEmail()
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

        $access = json_decode($response->getBody()->getContents());

        $responseBillet = $client->get('https://erp.agetelecom.com.br:45715/external/integrations/thirdparty/GetBillet/5541623',[
            'headers' => [
                'Authorization' => 'Bearer '.$access->access_token
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

        $mail = Mail::mailer('portal')->to('carlos.neto@agetelecom.com.br')
                    ->send(new SendBilling('scpc',
                        'Aviso Urgente AGE Fibra: Cancelamento de Contrato em Breve! 📵',
                        'Carlos Neto',
                        $billetPath
                        ));

        dd($mail);

    }

    public function index(Request $request)
    {
        set_time_limit(20000000000);

        $userSync = new UserSync();

        $userSync->builder();

        return true;


//        return $this->testSendEmail();

//        $billingRule = new BuilderBillingRuleController();
////
//        return $billingRule->builder();

        $authorization = 'App b13815e2d434d294b446420e41d4f4e6-6c3b9fe0-a751-45d5-aba0-7afbe9fb28bd';
        $client = new Client();

        $response = $client->request('POST', 'https://j36lvj.api-us.infobip.com/whatsapp/1/message/template', [
            'headers' => [
                'Authorization' => $authorization, // Substitua {authorization} pelo token de autenticação real
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'json' => [
                'messages' => [
                    [
                        'from' => '556140404040',
                        'to' => '5561984700440',
                        'messageId' => Random::generate(24),
                        'content' => [
                            'templateName' => 'pre_cancelamento_1',
                            'templateData' => [
                                'body' => [
                                    'placeholders' => [
//                                        'Carlos Neto'
                                    ]
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
                        'callbackData' => 'Teste callback',
//                        'notifyUrl' => 'https://www.example.com/whatsapp',
//                        'urlOptions' => [
//                            'shortenUrl' => true,
//                            'trackClicks' => true,
//                            'trackingUrl' => 'https://example.com/click-report',
//                            'removeProtocol' => true,
//                            'customDomain' => 'example.com'
//                        ]
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

        $body = $response->getBody();

        return $body;
////
//        return Report::getAllSending();
//
//        return true;
//
        $consult = false;
//
        $template = Template::find(1);

        // Configurar o cliente Guzzle
        $client = new Client([
            'base_uri' => 'https://j36lvj.api-us.infobip.com/',
            'timeout' => 10.0,
        ]);

        // Obter o token de autorização
        $authorization = 'App b13815e2d434d294b446420e41d4f4e6-6c3b9fe0-a751-45d5-aba0-7afbe9fb28bd';

        if(! $consult) {
            // Enviar a solicitação POST com Guzzle
            $response = $client->post('/sms/2/text/advanced', [
                'headers' => [
                    'Authorization' => $authorization,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    "bulkId" => 'Regua dia 07/05/2024',
                    "messages" => [
                        [
                            "destinations" => [
                                [
                                    "to" => "5561984700440"
                                ]
                            ],
                            "from" => "Age Telecom",
                            "text" => "teste api infoBip - " . Carbon::now()->format('d/m/Y H:i:s'),
                            'entityId' => 'portal_agetelecom_colaborador',
                            'applicationId' => 'portal_agetelecom_colaborador'
                        ]
                    ],
                ]
            ]);

            // Obter a resposta como JSON
            $responseData = json_decode($response->getBody(), true);


            $reportSms = new Report();
            $reportSmsLog = new ReportLog();


            foreach($responseData['messages'] as $k => $v) {

                $resultReport = $reportSms->create([
                    'bulk_id' => isset($responseData['bulkId']) ? $responseData['bulkId'] : 'envio_individual',
                    'mensagem_id' => $v['messageId'],
                    'canal' => 'SMS',
                    'contrato_id' => 10291,
                    'fatura_id' => 210213,
                    'celular' => $v['to'],
                    'celular_voalle' => $v['to'],
                    'email' => 'carlos.neto@agetelecom.com.br',
                    'segregacao' => 'prata',
                    'regra' => 10,
                    'status' => 100,
                    'status_descricao' => 200,
                    'template_sms_id' => 1
                ]);

                $reportSmsLog->create([
                    'envio_id' => $resultReport->id,
                    'bulk_id' => isset($responseData['bulkId']) ? $responseData['bulkId'] : 'envio_individual',
                    'mensagem_id' => $v['messageId'],
                    'enviado_para' => $v['to'],
                    'resposta_webhook' => json_encode($responseData),
                    'status' => 1
                ]);

            }


            return response()->json([
                'status' => $response->getStatusCode(),
                'data' => $responseData,
                'template' => $template
            ]);
        } else {


        $response = $client->get(
            'sms/1/inbox/reports',
            [
                'headers' => [
                    'Authorization' => $authorization,
                    'Accept' => 'application/json',
                ],
                'query' => [
                    'limit' => 10,
                    'bulkId' => '4144191653446471473056'
                ],
            ]
        );


        // Obter a resposta como JSON
        $responseData = json_decode($response->getBody(), true);

        return response()->json([
            'status' => $response->getStatusCode(),
            'data' => $responseData,
        ]);


        }



        return true;

        // Configurar o cliente Guzzle
        $client = new Client([
            'base_uri' => 'https://j36lvj.api-us.infobip.com/',
            'timeout' => 10.0,
        ]);

        // Obter o token de autorização
        $authorization = 'App b13815e2d434d294b446420e41d4f4e6-6c3b9fe0-a751-45d5-aba0-7afbe9fb28bd';

        // Enviar a solicitação POST com Guzzle
        $response = $client->post('sms/2/text/advanced', [
            'headers' => [
                'Authorization' => $authorization,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
                'bulkId' => 'Confirmação SMS 1',
                'messages' => [
                    [
                        'destinations' => [
                            ['to' => '+5561984700440'],
                            ['to' => '+5561991659351'],
                        ],
                        'from' => 'Age Telecom',
                        'text' => 'Teste age - infoBip' . Carbon::now()->format('d/m/Y H:i:s'),
                    ],
                ],
            ],
        ]);

        // Obter a resposta como JSON
        $responseData = json_decode($response->getBody(), true);

        return $responseData;

        return response()->json([
            'status' => $response->getStatusCode(),
            'data' => $responseData,
        ]);


        return true;
        $billingRule = new BuilderBillingRuleController();

        return $billingRule->builder();



        $client = new Client();

//        $responseTest = $client->get('https://www.orimi.com/pdf-test.pdf',[]);
//
//        return $responseTest->getBody();

        $responseBillet = $client->get('https://erp.agetelecom.com.br:45715/external/integrations/thirdparty/GetBillet/5541623',[
            'headers' => [
                'Authorization' => 'Bearer eyJhbGciOiJSUzI1NiIsImtpZCI6IjBBRjZDREEyRDU0MTRDRTY1MUM0RTk3NTM3QTFGNEY0QTMyNUQ5QTMiLCJ0eXAiOiJKV1QiLCJ4NXQiOiJDdmJOb3RWQlRPWlJ4T2wxTjZIMDlLTWwyYU0ifQ.eyJuYmYiOjE3MTM0NTkxMDAsImV4cCI6MTcxMzQ2MjcwMCwiaXNzIjoiaHR0cHM6Ly9lcnAuYWdldGVsZWNvbS5jb20uYnI6NDU3MDAiLCJhdWQiOlsiaHR0cHM6Ly9lcnAuYWdldGVsZWNvbS5jb20uYnI6NDU3MDAvcmVzb3VyY2VzIiwic3luZ3ciXSwiY2xpZW50X2lkIjoiMWZhODYzOTEtZTQ3ZC00YWFiLWEzZjAtM2M0NWY2OTI3Yzg4IiwiaWQiOiIxMTg5IiwibG9naW4iOiJkZWllZ29AYWdldGVsZWNvbS5jb20uYnIiLCJtb2RlIjoic3lzdGVtIiwibmFtZSI6IkRlaWVnbyIsInBlcnNvbmVtYWlsIjoiZGllZ28ubGltYUBhZ2V0ZWxlY29tLmNvbS5iciIsInBlcnNvbmlkIjoiMTMwMTkxIiwicGVyc29ubmFtZSI6IkRlaWVnbyIsInBsYWNlaWQiOiIiLCJwcm9maWxlaWQiOiI2MiIsInN5bmRhdGEiOiJUV3BOTVU5RVl6VmFha2sxVDBkU2FVMVVTbXhhYWxwcldsZEZkMDB5U1RGWlYxSnNUVEpSTUZwdFVUMDZXbGhzUzFaSFZsaE9WV3hwVFRBMGQxTlhjSFpoVlRGeFVWUktUV0ZyYkROVWEwMHdaVlUxUlZvelZsQlNSbXh3VkVWT1MxWkhWbGhPVlZaYVlWVnJNbE5YTVZOaFZuQllUVmhrVGxKRlJYZFVNRkp5WVZkYVVsQlVNRDA2V2xSb2EwMXFUVEZaYW1zd1dYcHNhVTVFVG0xYVJHY3pUVVJzYTAxcVdUSlpla0Y0VFVkTk0wMUhWVDA9IiwidHhpZCI6Ijk2ODE3NTY5MDg3IiwidHlwZXR4aWQiOiIyIiwibW9kdWxlcyI6IjEsMyw0LDUsNiw4LDEwLDEyLDE1LDE2LDIxLDI2LDI3LDI4LDMxLDMyLDUwLDUzLDU1LDU2LDU4LDU5LDk5LDE1MywxNjEsMTcwLDE3NSIsImlzT21uaSI6IkZhbHNlIiwidHlwZSI6ImludGVncmF0aW9uIiwiaW50ZWdyYXRpb24iOiJ0aGlyZHBhcnR5Iiwic3ViIjoiZGVpZWdvQGFnZXRlbGVjb20uY29tLmJyIiwic2NvcGUiOlsic3luZ3ciXX0.dYxdYYlEKJ4VD-tSifilx7vXknQTeiHz9hpyQlzOnx9_WdbRt7iPtJa5zf3_AmfdFYbzyNTG7JolIESTJDC8Pa-hEdSys51oVGs5a-d5kAq5BZRQ5OtCg7Ajl8D5uhzFzItasQ1hoBgA2HOY_mZ-s8QfBGpcIPRK7imfQqcrHSKq26zZeFwhlq4oJiQQLbAnpMTUX2iNzfu6d3bfomDHcYnCO3wGT3QHySrzWV9rvNl48q8bJ02_oAzzebwS4xzwMMRIwt7DsKKNN0qletRAxMKEctOksXebHXFvUVk2pzlhH95X9Nbm3S4jOCaTNX2R7kkoaNVOHHu_J5ChmkFRqspTRTmzBNglrm7kPcBid_0mUQzckOLaE4hkiagBo8qW-lAq4Pw7FFFGsF9Q3fKxTWOMrq7nlHQ_J3hvyiOXS8hTzeqOv--9EKBCdNIRrjZMGo6qDHnarDDFaEDCpyz0j3Ebz_WeYFLfZKIVp4A5WYtTMOV-16RKZJxYqb3J6YwPz_MgziSp2OdFx-rxaTGro8W0WCdXHz15kdpkcHjjH1UrQEj49U8qTbFiQnQCcnUN7YFDo_dekqiQrLOJw2PHnD09t-lcQwNv-rtumLtqdXW127X70fsQLG5gqSCrviyfGGNGrx63jrQ38XIWfWlpqXc1RMGW6mcBM0Es-CnR73g'
            ]
        ]);

        return $responseBillet->getBody()->getContents();

        $billingRule = new BuilderBillingRuleController();

        return $billingRule->builder();





        return true;


        $configuration = new Configuration(
            host: 'http://j36lvj.api-us.infobip.com/',
            apiKey: 'b13815e2d434d294b446420e41d4f4e6-6c3b9fe0-a751-45d5-aba0-7afbe9fb28bd'
        );




//        $whatsAppApi = new WhatsAppApi(config: $configuration);
//
//        $message = new WhatsAppMessage(
//            from: '5561984700440',
//            to: '',
//            content: new WhatsAppTemplateContent(
//                templateName: 'welcome_multiple_languages',
//                templateData: new WhatsAppTemplateDataContent(
//                    body: new WhatsAppTemplateBodyContent(
//                        placeholders: ['Age Telecom']
//                    )
//                ),
//                language: 'en'
//            ));
//
//        $bulkMessage = new WhatsAppBulkMessage(messages: [$message]);
//
//        $messageInfo = $whatsAppApi->sendWhatsAppTemplateMessage($bulkMessage);


//        $sendSmsApi = new SmsApi(config: $configuration);
//
//        $message = new SmsTextualMessage(
//            destinations: [
//                new SmsDestination(to: '+536184700440')
//            ],
//            from: 'InfoSMS',
//            text: 'Teste de SMS usando InfoBip - Hora do envio'. Carbon::now()->format('d/m/Y H:i:s')
//        );
//
//        $request = new SmsAdvancedTextualRequest(messages: [$message]);
//
//        try {
//            $smsResponse = $sendSmsApi->sendSmsMessage($request);
//        } catch (ApiException $apiException) {
//            // HANDLE THE EXCEPTION
//        }
//

        dd($smsResponse);


    }

    private function getQuery(): string
    {
        return <<<SQL
                SELECT
                c.id AS "contract_id",
                p.email AS "email",
                p.v_name AS "name",
                frt.document_amount,
                p.tx_id,
                CASE
                    WHEN p.cell_phone_1 IS NOT NULL THEN p.cell_phone_1
                    ELSE p.cell_phone_2
                END AS "phone",
                frt.typeful_line AS "barcode",
                frt.expiration_date AS "expiration_date",
                frt.competence AS "competence",
                case
                    when frt.expiration_date > current_date then -(frt.expiration_date - current_date)
                    else (current_date - frt.expiration_date)
                end as "days_until_expiration",
                frt.id as "frt_id"
            FROM erp.contracts c
            LEFT JOIN erp.people p ON p.id = c.client_id
            LEFT JOIN erp.financial_receivable_titles frt ON frt.contract_id = c.id
            WHERE
                c.v_stage = 'Aprovado'
                and c.v_status != 'Cancelado'
                AND frt.competence >= '2023-05-01'
                AND frt.deleted IS FALSE
                AND frt.finished IS FALSE
                AND frt.title LIKE '%FAT%'
                and frt.p_is_receivable is true
                and frt.typeful_line is not null
                and c.id = 34263
            limit 100
            SQL;


    }


    private function sendEmail($dataEmail)
    {
        $templates = $this->getTemplates();
        $sendings = ['success' => [], 'count' => 0, 'error' => []];

        $data = collect($dataEmail)->unique('email');
        $accessToken = $this->getAccessToken();

        foreach ($data as $value) {
            $billetPath = $this->fetchBillet($value, $accessToken);
            if ($billetPath) {
                $this->sendAppropriateEmails($value, $templates, $sendings, $billetPath);
                @unlink($billetPath); // Clean up after sending
            }
        }

        return $templates; // For auditing or logging
    }

    private function getTemplates()
    {
        return [
            [
                'rule' => -5,
                'view' => 'pre_expiration_5',
                'subject' => 'Lembrete - Vencimento da sua fatura Age Telecom em 5 dias',
                'sendings' => 0,
                'extract' => [],
                'title' => 'Pré vencimento 5 dias'
            ],
            [
                'rule' => -4,
                'view' => 'pre_expiration_4',
                'subject' => 'Lembrete - Vencimento da sua fatura Age Telecom em 4 dias',
                'sendings' => 0,
                'extract' => [],
                'title' => 'Pré vencimento 4 dias'
            ],
        ];
    }

    private function getAccessToken()
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

        $access = json_decode($response->getBody()->getContents());

        return $response->getStatusCode() == 200 ? $access->access_token : null;
    }

    private function fetchBillet($customerData, $accessToken)
    {
        $client = new Client();


        $response = $client->get('https://erp.agetelecom.com.br:45715/external/integrations/thirdparty/GetBillet/' . $customerData->frt_id, [
            'headers' => [
                "Authorization" => "Bearer $accessToken",
            ]
        ]);

        if ($response->getStatusCode() === 200) {
            $pdfContent = $response->getBody();

            $filePath = storage_path('app/portal/agecommunicate/rule/billing/boleto.pdf');

            // Salve o arquivo no caminho especificado
            file_put_contents($filePath, $pdfContent);

            return $filePath;
        }

        return null;
    }

    private function sendAppropriateEmails($customerData, &$templates, &$sendings, $billetPath)
    {
        $dateFormatted = Carbon::now()->isoFormat('D [de] MMMM [de] YYYY');

        foreach ($templates as &$template) {
            if ($this->matchRule($customerData, $template['rule'])) {
                // Implement email sending logic here. Example:
                \Mail::mailer('fat')->to('carlos.neto@agetelecom.com.br')->send(new SendBilling($template['view'], $template['subject'], $customerData, $billetPath));

                $template['sendings']++;
                $sendings['success'][] = ['template' => $template['view'], 'client' => $customerData];
                $sendings['count']++;
                break; // Assuming one email per customer per run
            }
        }
    }

    private function matchRule($customerData, $rule)
    {
        if (is_array($rule)) {
            return in_array($customerData->days_until_expiration, $rule);
        }

        return $customerData->days_until_expiration == $rule;
    }

}

