<?php

namespace App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Communicate;

use App\Http\Controllers\Integrator\Aniel\Schedule\_aux\CapacityAniel;
use App\Models\Integrator\Aniel\Schedule\Communicate;
use App\Models\Integrator\Aniel\Schedule\CommunicateLog;
use App\Models\Integrator\Aniel\Schedule\CommunicateMirror;
use App\Models\Integrator\Aniel\Schedule\ImportOrder;
use App\Models\Integrator\Aniel\Schedule\SubService;
use App\Models\Portal\AgeCommunicate\BillingRule\Integrator;
use Carbon\Carbon;
use GuzzleHttp\Client;

class InfoOrder
{
    public function __invoke()
    {
        set_time_limit(2000000);

        return $this->watchStatusOrders();
    }

    public function sendConfirmation($data)
    {
        // Modelo de $data
//        $data = [
//            'os_id' => 5234,
//            'protocolo' => '1153840',
//            'celular_1' => '5561998003186',
//            'hora_inicio' => '09:00',
//            'hora_fim' => '13:00',
//            'data_agendamento' => '31/07/2024'
//        ];

        $integrator = (new Integrator())->whereTitulo('InfoBip')
        ->first();



        // Configurar o cliente Guzzle
        $client = new Client([
            'base_uri' => $integrator['configuracao']['configuration']['host'],
            'http_errors' => false
        ]);


        $response = $client->post( 'whatsapp/1/message/template', [
            'headers' => [
                'Authorization' => $integrator['configuracao']['configuration']['apiKey'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
                'messages' => [
                    [
                        'from' => '5561920023969',
                        'to' => $data['celular_1'],
                        'messageId' => '',
                        'content' => [
                            'templateName' => 'confirmacao_agendamento_portal',
                            'templateData' => [
                                'body' => [
                                    'placeholders' => [
                                        $data['protocolo'],
                                        $data['data_agendamento'],
                                        $data['hora_inicio'],
                                        $data['hora_fim']
                                    ]
                                ],
                                'buttons' => [
                                    ['type' => 'QUICK_REPLY','parameter' => 'Confirmar'],
                                    ['type' => 'QUICK_REPLY','parameter' => 'Falar com atendente']
                                ]
                            ],
                            'language' => 'pt_BR'
                        ],
                        'entityId' => 'portal_agetelecom_colaborador',
                        'applicationId' => 'portal_agetelecom_colaborador',
                        'callbackData' => '',
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

        $this->storeRegister($data, $responseData, 'confirmacao_agendamento_portal');

    }

    public function sendAlterOs($data)
    {

        // Modelo $data
//        $data = [
//            'os_id' => 5234,
//            'protocolo' => '1153840',
//            'celular_1' => '5561984700440',
//        ];

        $integrator = (new Integrator())->whereTitulo('InfoBip')
            ->first();


        // Configurar o cliente Guzzle
        $client = new Client([
            'base_uri' => $integrator['configuracao']['configuration']['host'],
            'http_errors' => false
        ]);


        $response = $client->post( 'whatsapp/1/message/template', [
            'headers' => [
                'Authorization' => $integrator['configuracao']['configuration']['apiKey'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
                'messages' => [
                    [
                        'from' => '5561920023969',
                        'to' => $data['celular_1'],
                        'messageId' => '',
                        'content' => [
                            'templateName' => 'informar_deslocamento_os_portal',
                            'templateData' => [
                                'body' => [
                                    'placeholders' => [
                                        $data['protocolo']
                                    ]
                                ],
                                'buttons' => [
                                    ['type' => 'QUICK_REPLY','parameter' => 'Confirmar'],
                                    ['type' => 'QUICK_REPLY','parameter' => 'Falar com atendente']
                                ]
                            ],
                            'language' => 'pt_BR'
                        ],
                        'entityId' => 'portal_agetelecom_colaborador',
                        'applicationId' => 'portal_agetelecom_colaborador',
                        'callbackData' => '',
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

        $this->storeRegister($data, $responseData, 'informar_deslocamento_os_portal');

    }

    private function storeRegister($data, $response, $template)
    {
        $communicate = new Communicate();

        $communicate->os_id = $data['os_id'];
        $communicate->protocolo = $data['protocolo'];
        $communicate->celular_cliente = $data['celular_1'];
        $communicate->template = $template;
        $communicate->dados = json_encode($data);
        $communicate->status_envio = 'enviado';
        $communicate->status_resposta = 'pendente';
        $communicate->mensagem_id = $response['messages'][0]['messageId'];
        $communicate->data_envio = Carbon::now();
        $communicate->save();

        $log = new CommunicateLog();

        $log->envio_id = $communicate->id;
        $log->status_envio = 'enviado';
        $log->status_resposta = 'pendente';
        $log->atualizado_em = Carbon::now();
        $log->save();

    }

    private function watchStatusOrders()
    {
        $this->storeOrdersAniel();
//        return $this->buildAlterOs();
    }

    private function storeOrdersAniel()
    {
        $ordersAnielToday = (new CapacityAniel(Carbon::now()->format('Y-m-d')))->getCapacityAniel()->toArray();
        $ordersAnielTomorrow = (new CapacityAniel(Carbon::tomorrow()->format('Y-m-d')))->getCapacityAniel()->toArray();
        $ordersAniel = array_merge($ordersAnielToday, $ordersAnielTomorrow);


        $subServices = SubService::where('servico_id', '!=', 1)->get();
        $communicateMirror = new CommunicateMirror();

        foreach($ordersAniel as $order) {

            foreach($subServices as $service) {


                $serviceAniel = mb_convert_case($order->TIPO_SERVICO_ANIEL, MB_CASE_LOWER, 'UTF-8');
                $serviceVoalle = mb_convert_case($service->titulo, MB_CASE_LOWER, 'UTF-8');

                if($serviceAniel == $serviceVoalle) {

                    $voalleOrder = ImportOrder::where('protocolo', $order->N_OS)->first();


                    if ($voalleOrder) {
                        $dateSchedule = $order->Data_do_Agendamento . ' ' . $order->Hora_do_Agendamento;

                        $communicateMirror->updateOrCreate(
                            ['os_id' => $voalleOrder->id], // Condição para encontrar o registro
                            [
                                'protocolo' => $order->N_OS,
                                'status_aniel' => $order->Status,
                                'status_aniel_descricao' => $order->Status_Descritivo,
                                'data_agendamento' => Carbon::parse($dateSchedule)
                            ]
                        );
                    }

                }
            }

        }

    }

    private function buildAlterOs()
    {
        $getOrders = CommunicateMirror::where('status_aniel', 6)
                    ->where('envio_deslocamento', false)
                    ->get();

        return $getOrders;

    }


}
