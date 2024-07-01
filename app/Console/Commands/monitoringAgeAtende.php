<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Nette\Utils\Random;

class monitoringAgeAtende extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:monitoring-age-atende';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitoramento Age Atende';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $client = new Client();


        $response = $client->post('http://10.25.3.196:3000/signin/login', [
            'json' => [
                'username' => 'carlos.neto',
                'password' => env('PASSWORD_MONITORING')
            ]
        ]);

        if($response->getStatusCode() != 201) {
            return true;
        }


        $params =
            [
                'AgeAtende',
                Carbon::now()->format('Y-m-d H:i:s'),
                'DOWN',
                'As tentativas de conexão com a aplicação falharam.',
                'Reinicialização do serviço/servidor',
                'Desenvolvimento',
                'Urgente'
            ];

        $destinations = [
            '5561984700440',
//            '5561999353292',
//            '5561998003186',
//            '5561998051731',
//            '5561992587560',
//            '5561991210156'
        ];


        foreach($destinations as $destination) {
            // Configurar o cliente Guzzle
            $client = new Client([
                'base_uri' => 'http://j36lvj.api-us.infobip.com/',
                'http_errors' => false, // Impedir que Guzzle gere exceções para códigos de erro HTTP
            ]);

            $response = $client->post( 'whatsapp/1/message/template', [
                'headers' => [
                    'Authorization' => 'App b13815e2d434d294b446420e41d4f4e6-6c3b9fe0-a751-45d5-aba0-7afbe9fb28bd',
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'messages' => [
                        [
                            'from' => '556140404070',
                            'to' => $destination,
                            'messageId' => Random::generate(24),
                            'content' => [
                                'templateName' => 'aviso_monitoramento_sistemas',
                                'templateData' => [
                                    'body' => [
                                        'placeholders' => $params ?? []
                                    ]
                                ],
                                'language' => 'pt_BR'
                            ],
                            'entityId' => 'portal_agetelecom_colaborador',
                            'applicationId' => 'portal_agetelecom_colaborador',
                            'callbackData' => ''
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


        }

    }
}
