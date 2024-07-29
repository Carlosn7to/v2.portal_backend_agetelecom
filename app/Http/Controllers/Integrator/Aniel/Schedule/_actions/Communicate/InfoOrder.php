<?php

namespace App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Communicate;

use App\Models\Portal\AgeCommunicate\BillingRule\Integrator;
use GuzzleHttp\Client;

class InfoOrder
{
    public function __invoke()
    {
        // TODO: Implement __invoke() method.
    }

    public function sendConfirmation()
    {

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
                        'to' => '5561984700440',
                        'messageId' => '',
                        'content' => [
                            'templateName' => 'confirmacao_agendamento_portal',
                            'templateData' => [
                                'body' => [
                                    'placeholders' => [
                                        '17292712',
                                        '29/07/2024',
                                        '09:00',
                                        '12:00'
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

        return $responseData;

    }

    public function sendAlterOs()
    {

    }

    private function watchStatusOrders()
    {


    }

}
