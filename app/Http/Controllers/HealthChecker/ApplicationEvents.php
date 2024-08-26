<?php

namespace App\Http\Controllers\HealthChecker;

use App\Models\HealthChecker\App;
use App\Models\HealthChecker\AppEvent;
use Carbon\Carbon;

class ApplicationEvents
{
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

}
