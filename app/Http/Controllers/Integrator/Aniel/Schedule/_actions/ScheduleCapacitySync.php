<?php

namespace App\Http\Controllers\Integrator\Aniel\Schedule\_actions;

use App\Models\Integrator\Aniel\Schedule\Capacity;
use App\Models\Integrator\Aniel\Schedule\CapacityWeekly;
use App\Models\Integrator\Aniel\Schedule\Service;
use Carbon\Carbon;

class ScheduleCapacitySync
{

    public function sync($data)
    {

        foreach($data as $key => $value) {

            foreach($value['capacity'] as $service => $valueCapacity) {

                foreach($valueCapacity as $period => $capacity) {

                    $periodMap = [
                        'manha' => 1,
                        'tarde' => 2,
                        'noite' => 3
                    ];

                    $capacityFind = Capacity::where('data', $key)
                        ->where('dia_semana', $value['dayName'])
                        ->where('periodo', $periodMap[$period])
                        ->where('servico', $service)
                        ->first();

                    if($capacityFind) {

                        $infoSchedule = $this->buildScheduleStatus($capacityFind);


                        if($infoSchedule != null) {
                            $capacityFind->update([
                                'status' => $infoSchedule['status'],
                                'motivo_fechamento' => $infoSchedule['description'],
                                'data_fechamento' => Carbon::now()->format('Y-m-d'),
                                'hora_fechamento' => Carbon::now()->format('H:i:s'),
                                'atualizado_por' => 1
                            ]);
                        }


                        $capacityFind->update([
                            'utilizado' => $capacity['used'],
                        ]);

                    } else {
                        Capacity::create([
                            'data' => $key,
                            'dia_semana' => $value['dayName'],
                            'servico' => $service,
                            'periodo' => $periodMap[$period],
                            'hora_inicio' => $capacity['start'],
                            'hora_fim' => $capacity['end'],
                            'capacidade' => $capacity['capacity'],
                            'utilizado' => $capacity['used'],
                            'status' => 1,
                            'atualizado_por' => auth('portal')->user()->id
                        ]);
                    }

                }

            }

        }

    }

    private function buildScheduleStatus($schedule)
    {

        if($schedule->status == 'aberta') {

            $overCapacity = $schedule->utilizado >= $schedule->capacidade ? 1 : 0;

            if($overCapacity) {
                $info = [
                    'status' => 'fechada',
                    'description' => 'Capacidade atingida'
                ];

                return $info;
            }

            $service = Service::whereTitulo($schedule->servico)->first();

            $capacityWeekly = CapacityWeekly::whereServicoId($service->id)
                ->whereDiaSemana($schedule->dia_semana)
                ->get(['servico_id', 'dia_semana', 'hora_fim']);

            $hourActual = Carbon::now();
            $diffTarget = -3;
            foreach($capacityWeekly as $key => $value) {
                $horaFim = Carbon::createFromFormat('H:i:s', $value->hora_fim);

                // Calcula a diferença em horas
                $diffInHours = $horaFim->diffInHours($hourActual, false); // A flag 'false' garante que a diferença negativa seja considerada

                if ($diffInHours >= $diffTarget) {

                    $info = [
                        'status' => 'fechada',
                        'description' => 'O período de agendamento expirou.'
                    ];

                    return $info;
                }
            }



        }

        return null;

    }
}
