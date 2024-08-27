<?php

namespace App\Http\Controllers\HealthChecker;

use App\Http\Controllers\Controller;
use App\Models\HealthChecker\AppResource;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BuilderController extends Controller
{
    public function storeStatistics(Request $request)
    {


        $validator = \Validator::make($request->all(),[
            'aplicacao_id' => 'required|integer',
            'cpu_nucleos_totais' => 'required|integer',
            'cpu_uso' => 'required|numeric',
            'ram_total' => 'required|integer',
            'ram_uso' => 'required|integer',
            'disco_total' => 'required|integer',
            'disco_uso' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $appResource = new AppResource();
        $hour_minute = Carbon::now()->format('H:i');

        $appResource->firstOrCreate(
            [
                'aplicacao_id' => $request->aplicacao_id,
                'hora_minuto' => $hour_minute
            ],
            [
                'aplicacao_id' => $request->aplicacao_id,
                'cpu_nucleos_totais' => $request->cpu_nucleos_totais,
                'cpu_uso' => $request->cpu_uso < 1 ? 1 : $request->cpu_uso,
                'cpu_disponivel' => 100 - $request->cpu_uso,
                'ram_total' => $request->ram_total,
                'ram_uso' => $request->ram_uso,
                'disco_total' => $request->disco_total,
                'disco_uso' => $request->disco_uso,
                'hora_minuto' => $hour_minute
            ]
        );

        return response()->json(['message' => 'Statistics stored successfully'], 200);

    }

    public function getStatus()
    {
        $events = (new ApplicationEvents())->getEventsForStatus();

        return $events;
    }

    public function getLastEvents()
    {
        $events = (new ApplicationEvents())->getEvents();

        return $events;

    }

    public function getAnalyticStatistics() : array
    {
        $lastUsage = AppResource::whereDate('created_at', Carbon::now()->format('Y-m-d'))
            ->where('hora_minuto', '>=', Carbon::now()->subHour()->format('H:i'))
            ->with('application')
            ->orderBy('hora_minuto', 'asc')
            ->get();

        $groupedByName = collect();

        foreach ($lastUsage as $resource) {
            $nome = $resource->application->nome;

            if (!$groupedByName->has($nome)) {
                $groupedByName->put($nome, collect());
            }

            $groupedByName[$nome]->push($resource);
        }

        foreach ($groupedByName as $nome => $resources) {
            foreach ($resources as $resource) {
                unset($resource->application);
            }
        }

        $now = Carbon::now();
        $minutes = collect();
        for ($i = 0; $i <= 60; $i++) {
            $minutes->push($now->copy()->subMinutes($i)->format('H:i'));
        }

        foreach($minutes as $minute) {
            foreach ($groupedByName as $nome => $resources) {
                $found = false;
                foreach ($resources as $resource) {
                    if ($resource['hora_minuto'] === $minute.':00') {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $groupedByName[$nome]->push([
                        'hora_minuto' => $minute,
                        'cpu_uso' => 0,
                        'cpu_disponivel' => 100,
                        'ram_uso' => 0,
                        'disco_uso' => 0
                    ]);
                }
            }
        }

        // Ordenando pela hora_minuto
        foreach ($groupedByName as $nome => $resources) {
            $groupedByName[$nome] = $resources->sortBy('hora_minuto')->values();
        }

        return $groupedByName->toArray();

    }
}
