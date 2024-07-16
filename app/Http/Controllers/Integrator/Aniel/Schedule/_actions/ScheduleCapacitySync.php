<?php

namespace App\Http\Controllers\Integrator\Aniel\Schedule\_actions;

use App\Models\Integrator\Aniel\Schedule\Capacity;

class ScheduleCapacitySync
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function sync()
    {

        foreach($this->data as $key => $value) {

            foreach($value['capacity'] as $service => $valueCapacity) {


                foreach($valueCapacity as $period => $capacity) {

                    $periodMap = [
                        'manha' => 1,
                        'tarde' => 2,
                        'noite' => 3
                    ];

                    $capacityFind = Capacity::where('data', $value['period'])
                        ->where('dia_semana', $value['dayName'])
                        ->where('periodo', $periodMap[$period])
                        ->where('servico', $service)
                        ->first();


                    if($capacityFind) {
                        $capacityFind->update([
                            'capacidade' => $capacity['capacity'],
                            'utilizado' => $capacity['used'],
                        ]);

                    } else {
                        Capacity::create([
                            'data' => $value['period'],
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

}
