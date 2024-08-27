<?php

namespace App\Http\Controllers\HealthChecker;

use App\Models\HealthChecker\App;
use App\Models\HealthChecker\AppEvent;
use App\Models\HealthChecker\AppStatisticResponse;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

class ApplicationEvents
{
    public function __invoke()
    {
        $this->watchEvents();
    }

    public function getEventsForStatus()
    {
        $applications = App::whereMonitoramento(true)
                            ->get(['id', 'nome']);


        $result = [];

        foreach($applications as $app) {
            $result[] = [
                'id' => $app->id,
                'name' => $app->nome,
                'events' => $this->getEventForStatus($app->id)
            ];
        }

        return $result;


    }


    private function getEventForStatus($id)
    {
        // Recuperar todos os eventos da aplicação específica, ordenados por data
        $eventos = AppEvent::where('aplicacao_id', $id)
            ->orderBy('created_at')
            ->get();

        $ultimoStatus = null;
        $ultimoTimestamp = null;
        $description = '';

        foreach ($eventos as $evento) {
            if ($ultimoStatus === null || $evento->status !== $ultimoStatus) {
                // Atualizar o último status e timestamp
                $ultimoStatus = $evento->status;
                $ultimoTimestamp = $evento->created_at;
                $description = $evento->descricao;
            }
        }

        // Retorna o status mais recente e seu timestamp
        return [
            'status' => $ultimoStatus,
            'created_at' => Carbon::parse($ultimoTimestamp)->format('Y-m-d H:i:s'),
            'description' => $description
        ];
    }

    public function getEvents()
    {
        $events = AppEvent::with('application')->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();


        return response()->json($events, 200);
    }

    public function watchEvents()
    {
        $user = 'carlos.neto';
        $password = env('PASSWORD_TRACKING');

        $dataApps = [
            'portal' => [
                'id' => 1,
                'uri' => 'https://v2.ageportal.agetelecom.com.br/portal/auth/login',
                'form' => [
                    'user' => $user,
                    'password' => $password
                ]
            ],
            'ageAtende' => [
                'id' => 3,
                'uri' => 'http://10.25.3.196:3000/signin/login',
                'form' => [
                    'username' => $user,
                    'password' => $password
                ]
            ],
            'nexus' => [
                'id' => 2,
                'uri' => 'http://192.168.69.80/api/auth/token',
                'form' => [
                    'username' => $user,
                    'password' => $password,
                    'grant_type' => 'password',
                    'client_id' => 'gateway',
                    'client_secret' => 'Xi1LpoXpKsfflugYqFDGkuOn5jZG58z9'
                ]
            ]
        ];

        $this->watchApp($dataApps);

    }

    private function watchApp($dataApps)
    {
        $client = new Client();

        foreach($dataApps as $app => $data) {
            $startTime = microtime(true);
            $responseTime = new AppStatisticResponse();

            try {

                $dataError = [
                    'user' => $data['form']['user'] ?? $data['form']['username'],
                    'password' => \Hash::make($data['form']['password']),
                    'uri' => $data['uri'],
                    'code' => 0,
                    'message' => ''
                ];


                $response = $client->post($data['uri'], [
                    'form_params' => $data['form'],
                    'timeout' => 30, // Timeout de 30 segundos
                ]);

                $endTime = microtime(true);
                $executionTime = ($endTime - $startTime) * 1000;

                $dataError['execution_time'] = $executionTime;


                $responseTime->create([
                    'aplicacao_id' => $data['id'],
                    'status_codigo' => $response->getStatusCode(),
                    'tempo_resposta' => $executionTime,
                ]);

                $lastEvent = AppEvent::where('aplicacao_id', $data['id'])
                    ->orderBy('created_at', 'desc')
                    ->first();

                if($lastEvent->status === 'offline') {
                    $event = new AppEvent();
                    $event->create([
                        'aplicacao_id' => $data['id'],
                        'status' => 'online',
                        'evento' => 'aplicacao',
                        'tipo' => 'informativo',
                        'descricao' => 'Aplicação online.',
                        'dados' => json_encode($dataError)
                    ]);
                }

            } catch (ConnectException $e) {
                $dataError['code'] = $e->getCode();
                $dataError['message'] = $e->getMessage();
                // Tratamento específico para falha de conexão, como erro de DNS
                $event = new AppEvent();
                $event->create([
                    'aplicacao_id' => $data['id'],
                    'status' => 'offline',
                    'evento' => 'aplicacao',
                    'tipo' => 'erro',
                    'descricao' => 'Falha de conexão.',
                    'dados' => json_encode($dataError)
                ]);

                $endTime = microtime(true);
                $executionTime = ($endTime - $startTime) * 1000;

                $dataError['execution_time'] = $executionTime;

                $responseTime->create([
                    'aplicacao_id' => $data['id'],
                    'status_code' => $e->getCode(),
                    'tempo_resposta' => $executionTime,
                ]);

            } catch (RequestException $e) {

                $dataError['code'] = $e->getCode();
                $dataError['message'] = $e->getMessage();

                // Tratamento de outras exceções do Guzzle
                $event = new AppEvent();
                $event->create([
                    'aplicacao_id' => $data['id'],
                    'status' => 'offline',
                    'evento' => 'aplicacao',
                    'tipo' => 'erro',
                    'descricao' => 'Falha na requisição.',
                    'dados' => json_encode($dataError)
                ]);

                $endTime = microtime(true);
                $executionTime = ($endTime - $startTime) * 1000;

                $dataError['execution_time'] = $executionTime;

                $responseTime->create([
                    'aplicacao_id' => $data['id'],
                    'status_code' => $e->getCode(),
                    'tempo_resposta' => $executionTime,
                ]);


            } catch (\Exception $e) {

                $dataError['code'] = $e->getCode();
                $dataError['message'] = $e->getMessage();

                // Tratamento de qualquer outra exceção
                $event = new AppEvent();
                $event->create([
                    'aplicacao_id' => $data['id'],
                    'status' => 'offline',
                    'evento' => 'aplicacao',
                    'tipo' => 'erro',
                    'descricao' => 'Erro geral: ' . $e->getMessage(),
                    'dados' => json_encode($dataError)
                ]);

                $endTime = microtime(true);
                $executionTime = ($endTime - $startTime) * 1000;

                $dataError['execution_time'] = $executionTime;

                $responseTime->create([
                    'aplicacao_id' => $data['id'],
                    'status_code' => $e->getCode(),
                    'tempo_resposta' => $executionTime,
                ]);

            }
        }




    }

}
