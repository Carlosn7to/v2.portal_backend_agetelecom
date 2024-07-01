<?php

namespace App\Routines\Portal\Tracking\AgeAtende;

use App\Models\Portal\Structure\TrackingService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Nette\Utils\Random;

class TrackingAgeAtende
{
    private $findAlert;
    private $tracking;
    private $params;

    public function __construct()
    {

        $this->tracking = new TrackingService();
        $this->findAlert = $this->tracking->where('servico', 'AgeAtende')
            ->where('data_hora_resolucao', null)
            ->first();
    }

    public function getStatus()
    {
        $client = new Client();

        try {
            $response = $client->post('http://10.25.3.196:3000/signin/login', [
                'json' => [
                    'username' => 'carlos.neto',
                    'password' => config('services.portal.password_tracking')
                ],
                'timeout' => 10,
                'connect_timeout' => 10,
                'read_timeout' => 10
            ]);


            if($this->findAlert) {
                $this->findAlert->update([
                    'data_hora_resolucao' => Carbon::now()->format('Y-m-d H:i:s')
                ]);

                return $this->sendingReport('up');

            }

        } catch (\Exception $e) {
            if(!$this->findAlert) {
                return $this->sendingReport('down');
            }
        }


    }

    private function sendingReport($status)
    {
        $this->buildParams($status);

        foreach($this->params['destinatarios'] as $destination) {
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
                                        'placeholders' => [
                                            $this->params['servico'],
                                            $this->params['data_hora'],
                                            $this->params['status'],
                                            $this->params['detalhes'],
                                            $this->params['acoes'],
                                            $this->params['equipe'],
                                            $this->params['prioridade']
                                        ]
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


        $this->saveLog();

        return true;
    }

    private function saveLog()
    {


        if($this->findAlert) {
            return;
        }

        $this->tracking->create([
            'servico' => $this->params['servico'],
            'comando' => 'tracking:services',
            'descricao' => $this->params['detalhes'],
            'data_hora_alerta' => Carbon::now()->format('Y-m-d H:i:s'),
            'data_hora_resolucao' => null,
            'log' => json_encode($this->params)
        ]);
    }

    private function buildParams($status)
    {

        $destinations = [
            '5561984700440',
//            '5561999353292',
//            '5561998003186',
//            '5561998051731',
//            '5561992587560',
//            '5561991210156'
        ];

        $info = [
            'details' => [
                'up' => 'A aplicação está funcionando normalmente.',
                'down' => 'As tentativas de conexão com a aplicação falharam.'
            ],
            'actions' => [
                'up' => 'Identificar o problema que ocasionou a queda.',
                'down' => 'Reinicialização do serviço/servidor.'
            ],
            'team' => 'Desenvolvimento',
            'priority' => [
                'up' => 'Moderada',
                'down' => 'Urgente'
            ]
        ];

        $this->params = [
            'servico' => 'AgeAtende',
            'data_hora' =>Carbon::now()->format('Y-m-d H:i:s'),
            'status' => mb_convert_case("$status", MB_CASE_UPPER, 'UTF-8'),
            'detalhes' => $info['details'][$status],
            'acoes' => $info['actions'][$status],
            'equipe' => $info['team'],
            'prioridade' => $info['priority'][$status],
            'destinatarios' => $destinations
        ];
    }

}
