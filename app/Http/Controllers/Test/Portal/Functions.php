<?php

namespace App\Http\Controllers\Test\Portal;

use App\Events\AlertMessageAlterStatusEvent;
use App\Events\SendDataEvent;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Management\DashboardSchedule;
use App\Http\Controllers\Integrator\Aniel\Schedule\_actions\SubServicesSync;
use App\Http\Controllers\Portal\AgeCommunicate\BillingRule\BuilderBillingRuleController;
use App\Jobs\SendEmailGeneric;
use App\Mail\Portal\AgeCommunicate\Rule\Billing\SendBilling;
use App\Mail\Portal\Helpers\SendQuality;
use App\Mail\SendMaintenanceScheduled;
use App\Models\Integrator\Aniel\Schedule\Communicate;
use App\Models\Integrator\Aniel\Schedule\ImportOrder;
use App\Models\Integrator\Aniel\Schedule\OrderBroken;
use App\Models\Integrator\Aniel\Schedule\Service;
use App\Models\Integrator\Aniel\Schedule\StatusOrder;
use App\Models\Portal\AgeCommunicate\BillingRule\Reports\Report;
use App\Models\Portal\AgeCommunicate\BillingRule\Reports\ReportLog;
use App\Models\Portal\AgeCommunicate\BillingRule\Templates\Template;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Infobip\Configuration;
use Nette\Utils\Random;

class Functions extends Controller
{

    private $user;

    public function __construct()
    {
//        $this->middleware('portal.ageCommunicate.infoBip.access')->only('index');
    }

    public function index(Request $request)
    {
        set_time_limit(20000000000);




//        $array = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), $request->file('excel'));
//
//        $array = array_chunk($array[0], 500);
//
//        foreach($array as $emails) {
//
//
//            foreach($emails as $key => $email) {
//
//
//                $emailValidated = filter_var($email[0], FILTER_VALIDATE_EMAIL);
//
//                if($emailValidated) {
//
//                    $mail = (new SendMaintenanceScheduled())->onConnection('database')->onQueue('emails');
//
//                    \Mail::mailer('portal')->to($email[0])
//                        ->queue($mail);
//                }
//            }
//        }
//

        return true;

//        $result = \DB::connection('voalle')->select('select * from people p limit 1');
//
//        return $result;



//        broadcast(new AlertMessageAlterStatusEvent('Olá mundo'));
        broadcast(new SendDataEvent());
        return response()->json(['status' => 'Event sent!']);

//
//        $b2bSeller = new IdentifyClient();
//
//        return $b2bSeller->response();


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

    private function debugMirrorCapacity()
    {
        $startDate = Carbon::now()->subDays(10)->startOfDay();
        $uniqueDates = \App\Models\Integrator\Aniel\Schedule\Mirror::where('data_agendamento', '>=', $startDate)
            ->get(['data_agendamento'])
            ->map(function ($item) {
                return Carbon::parse($item->data_agendamento)->toDateString();
            })
            ->unique()
            ->values();


        $services = Service::where('titulo', '<>', 'Sem vinculo')->with(['subServices', 'capacityWeekly'])
            ->get();


        foreach ($uniqueDates as $date) {

            $ordersVoalle = ImportOrder::whereDate('data_agendamento', $date)
                ->get(['protocolo', 'tipo_servico', 'data_agendamento', 'node as localidade', 'status_id', 'cliente_id'])->toArray();


            $mirror = new \App\Models\Integrator\Aniel\Schedule\Mirror();
            $dashboardFunctions = new DashboardSchedule();

            foreach ($ordersVoalle as &$order) {

                $anielOrder = $dashboardFunctions->getDataUniqueOrder($order['protocolo']);
                $anielOrder = count($anielOrder) > 0 ? $anielOrder[0] : null;

                $dateScheduleAniel = null;

                if ($anielOrder) {

                    $dateScheduleAniel = Carbon::parse($anielOrder->Data_do_Agendamento . ' ' . $anielOrder->Hora_do_Agendamento)->format('d/m/Y H:i:s');
                    $order['status'] = $anielOrder->Status_Descritivo;
                    $statusDetails = StatusOrder::where('titulo', $order['status'])->first();
                    $order['responsavel'] = mb_convert_case($anielOrder->Nome_Tecnico, MB_CASE_TITLE, 'UTF-8');
                } else {
                    $statusDetails = StatusOrder::where('id', $order['status_id'])->first();
                }

                if ($statusDetails) {
                    $order['cor_indicativa'] = $statusDetails->cor_indicativa;
                    $order['status_descricao'] = $statusDetails->titulo;
                }

                $order['data_agendamento'] = $dateScheduleAniel ?? Carbon::parse($order['data_agendamento'])->format('d/m/Y H:i:s');
                $order['localidade'] = mb_convert_case($order['localidade'], MB_CASE_TITLE, 'UTF-8');
                $order['tipo_servico'] = mb_convert_case($order['tipo_servico'], MB_CASE_TITLE, 'UTF-8');

                $brokenOrder = OrderBroken::where('protocolo', $order['protocolo'])->first();

                if ($brokenOrder) {
                    $order['aprovador'] = $dashboardFunctions->getFormattedName($brokenOrder->aprovador_id);
                    $order['solicitante'] = $dashboardFunctions->getFormattedName($brokenOrder->solicitante_id);
                }

                $communicationFirstConfirm = Communicate::whereDate(
                    'data_envio',
                    '>=',
                    Carbon::createFromFormat('d/m/Y H:i:s', $order['data_agendamento'])->subDay()->format('Y-m-d')
                )->where('protocolo', $order['protocolo'])
                    ->whereTemplate('confirmacao_agendamento_portal')
                    ->first();

                $communicationSecondConfirm = Communicate::whereDate(
                    'data_envio',
                    '=',
                    Carbon::createFromFormat('d/m/Y H:i:s', $order['data_agendamento'])->format('Y-m-d')
                )->where('protocolo', $order['protocolo'])
                    ->whereTemplate('informar_deslocamento_os_portal')
                    ->first();
                $order['confirmacao_cliente'] = $communicationFirstConfirm
                    ? mb_convert_case($communicationFirstConfirm->status_resposta, MB_CASE_TITLE, 'UTF-8')
                    : '';

                $order['confirmacao_deslocamento'] = $communicationSecondConfirm
                    ? mb_convert_case($communicationSecondConfirm->status_resposta, MB_CASE_TITLE, 'UTF-8')
                    : '';

                $order['servico'] = ' ';

                foreach ($services as $key => $service) {
                    foreach ($service['subServices'] as $k => $v) {
                        $subServiceTitle = mb_convert_case($v->titulo, MB_CASE_LOWER, 'UTF-8');
                        $serviceTitle = mb_convert_case($service->titulo, MB_CASE_LOWER, 'UTF-8');

                        if ($subServiceTitle == mb_convert_case($order['tipo_servico'], MB_CASE_LOWER, 'UTF-8')) {
                            $order['servico'] = mb_convert_case($serviceTitle, MB_CASE_TITLE, 'UTF-8');
                            break;
                        }

                    }
                }

                dd($order);
            }


            foreach ($ordersVoalle as $order) {
                $mirror->updateOrCreate(
                    ['protocolo' => $order['protocolo']],
                    [
                        'cliente_id' => $order['cliente_id'],
                        'protocolo' => $order['protocolo'],
                        'servico' => $order['servico'],
                        'sub_servico' => $order['tipo_servico'],
                        'data_agendamento' => Carbon::createFromFormat('d/m/Y H:i:s', $order['data_agendamento'])->format('Y-m-d H:i:s'),
                        'localidade' => $order['localidade'],
                        'status' => $order['status'] ?? $order['status_descricao'],
                        'cor_indicativa' => $order['cor_indicativa'] ?? '#ccc',
                        'confirmacao_cliente' => $order['confirmacao_cliente'],
                        'confirmacao_deslocamento' => $order['confirmacao_deslocamento'],
                        'solicitante' => $order['solicitante'] ?? '',
                        'aprovador' => $order['aprovador'] ?? '',
                        'responsavel' => $order['responsavel'] ?? ''
                    ]
                );
            }
        }

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

    private function getQueryAniel()
    {
        return <<<SQL
            SELECT
                *
                FROM TB_TIPO_SERVICO_EQUIPE tse
        SQL;

        return <<<SQL
            SELECT DISTINCT
                dp.NUM_DOC AS "N OS",
                dp.PROJETO,
                dp.COD_TIPO_SERV,
                CASE WHEN dp.COD_TIPO_SERV in ('96' , '4', '92', '51', '133') THEN 'MUDANDANÇA DE ENDEREÇO'
                WHEN dp.COD_TIPO_SERV in ('3', '95','1', '2') THEN 'INSTALAÇÃO'
                WHEN dp.COD_TIPO_SERV in ('57','55', '56', '41', '59', '97', '16') THEN 'MANUTENÇÃO'
                WHEN dp.COD_TIPO_SERV IN ('52', '94', '8', '10','93','5', '11', '58', '9', '6', '7', '99', '12', '63', '53', '161', '163') THEN 'VISITA TÉCNICA'
                ELSE dp.COD_TIPO_SERV END AS "TIPO_SERVICO",
                c.RAZAO_SOCIAL AS "Nome Cliente",
                CASE WHEN e.NOME IS NULL THEN 'SEM TÉCNICO ATRIBUIDO'
                ELSE e.NOME END AS "Nome Tecnico",
                dp.DIA AS "Criacao",
                CASE WHEN ts.DESCRICAO like 'Manhã' THEN 'Manhã'
                ELSE 'Tarde' END AS "Turno",
                ts.DESCRICAO AS "Turno_True",
                dp.DATA_MAXIMA AS "Data_do_Agendamento"
                FROM TB_DOCUMENTO_PRODUCAO dp
                LEFT JOIN TB_CLIENTE c ON c.RAZAO_SOCIAL = dp.TITULAR
                LEFT JOIN TB_EQUIPE e ON e.EQUIPE = dp.EQUIPE
                LEFT JOIN TB_TURNO_SERVICO ts ON ts.ID = dp.COD_TURNO_SERV
                WHERE dp.DATA_MAXIMA BETWEEN '2024-06-18' AND '2024-06-18'
                AND dp.COD_TIPO_SERV NOT IN ('57','55', '56', '41', '59')
            ROWS 10
        SQL;

    }

}

