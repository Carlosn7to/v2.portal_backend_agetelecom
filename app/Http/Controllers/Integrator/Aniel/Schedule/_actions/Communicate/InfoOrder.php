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
//        $this->buildConfirmOs();
        $this->buildAlterOs();
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
                        $period = $order->Hora_do_Agendamento < '12:00:00' ? 'manhã' : 'tarde';

                        $start = $period == 'manhã' ? '08:00' : '13:00';
                        $end = $period == 'manhã' ? '12:00' : '18:00';

                        $dateSchedule = $order->Data_do_Agendamento . ' ' . $start;


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


        foreach($getOrders as $order) {

            $detailsOrder = ImportOrder::whereId($order->os_id)->first();


            $data = [
                'os_id' => $order->os_id,
                'protocolo' => $order->protocolo,
                'celular_1' => $this->sanitizeCellphone($detailsOrder->celular_1),
            ];



            $this->sendAlterOs($data);
            $order->envio_deslocamento = true;
            $order->save();
        }

        return $getOrders;

    }

    private function buildConfirmOs()
    {
        $getOrders = CommunicateMirror::where('status_aniel', 0)
            ->where('envio_deslocamento', false)
            ->where('envio_confirmacao', false)
            ->where('data_agendamento', '=', '2024-08-05 13:00:00')
            ->get();



        foreach($getOrders as $order) {

            $detailsOrder = ImportOrder::whereId($order->os_id)->first();


            $period = Carbon::parse($order->data_agendamento)->format('H:i') < '12:00:00' ? 'manhã' : 'tarde';


            $start = $period == 'manhã' ? '08:00' : '13:00';
            $end = $period == 'manhã' ? '12:00' : '18:00';



            $data = [
            'os_id' => $order->os_id,
            'protocolo' => $order->protocolo,
            'celular_1' => $this->sanitizeCellphone($detailsOrder->celular_1),
            'hora_inicio' => $start,
            'hora_fim' => $end,
            'data_agendamento' => Carbon::parse($order->data_agendamento)->format('d/m/Y')
            ];

            $this->sendConfirmation($data);
            $order->envio_confirmacao = true;
            $order->save();
        }

        return $getOrders;

    }


    private function formatCellphone($cellphone)
    {
        $cellphone = strlen($cellphone) <= 11 ? '55' . $cellphone : $cellphone;
        $cellphone = strlen($cellphone) == 12 ?
            substr_replace($cellphone, '9', 4, 0) :
            $cellphone;

        return $cellphone;
    }

    public function sanitizeCellphone($cellphone)
    {
        $cellphoneFormmated = preg_replace('/[^0-9]/', '', $cellphone);

        $dddValids = [
            11, 12, 13, 14, 15, 16, 17, 18, 19, // São Paulo
            21, 22, 24, // Rio de Janeiro
            27, 28, // Espírito Santo
            31, 32, 33, 34, 35, 37, 38, // Minas Gerais
            41, 42, 43, 44, 45, 46, // Paraná
            47, 48, 49, // Santa Catarina
            51, 53, 54, 55, // Rio Grande do Sul
            61, 62, 64, // Goiás
            63, // Tocantins
            65, 66, // Mato Grosso
            67, // Mato Grosso do Sul
            68, // Acre
            69, // Rondônia
            71, 73, 74, 75, 77, // Bahia
            79, // Sergipe
            81, 82, // Pernambuco
            83, // Paraíba
            84, // Rio Grande do Norte
            85, 88, // Ceará
            86, 89, // Piauí
            87, // Pernambuco
            91, 93, 94, // Pará
            92, 97, // Amazonas
            95, // Roraima
            96, // Amapá
            98, 99 // Maranhão
        ];

        if(!in_array(substr($cellphoneFormmated, 0, 2), $dddValids)){
            return false;
        }

        $cellphoneFormmated = $this->formatCellphone($cellphoneFormmated);


        return $cellphoneFormmated;
    }


}
