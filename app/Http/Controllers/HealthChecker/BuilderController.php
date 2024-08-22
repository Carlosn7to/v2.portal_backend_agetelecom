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
                'cpu_uso' => $request->cpu_uso,
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
}
