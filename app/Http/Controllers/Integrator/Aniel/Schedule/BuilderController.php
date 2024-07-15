<?php

namespace App\Http\Controllers\Integrator\Aniel\Schedule;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Integrator\Aniel\Schedule\_actions\SubServicesSync;
use App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Voalle\OrderSync;
use App\Http\Controllers\Integrator\Aniel\Schedule\_aux\CapacityAniel;
use App\Http\Controllers\Test\Portal\Aniel\API;
use App\Models\Integrator\Aniel\Schedule\Capacity;
use App\Models\Integrator\Aniel\Schedule\CapacityWeekly;
use App\Models\Integrator\Aniel\Schedule\Service;
use App\Models\Integrator\Aniel\Schedule\SubService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BuilderController extends Controller
{
    private $dataAniel;


    public function getCapacity(Request $request)
    {

        if($request->period == null) {
            return response()->json([
                'message' => 'O período é obrigatório!',
                'status' => 'Erro'
            ], 400);
        }

        $response = [
            'period' => Carbon::parse($request->period)->format('Y-m-d'),
            'dayName' => Carbon::parse($request->period)->locale('pt_BR')->isoFormat('dddd'),
            'capacity' => []
        ];

        $capacity = (new CapacityWeekly())->where('dia_semana', $response['dayName'])
            ->whereNull('data_final')
            ->with('service')
            ->get();


       $this->dataAniel = (new CapacityAniel($response['period']))->getCapacityAniel();

        foreach($capacity as $key => $value) {

            $period = $value->hora_inicio < '12:00:00' ? 'manha' : 'tarde';

            if(isset($response['capacity'][$value->service->titulo])) {
                $response['capacity'][$value->service->titulo][$period] = [
                    'start_period' => $value->hora_inicio,
                    'end_period' => $value->hora_fim,
                    'capacity' => $value->capacidade,
                    'used' => $this->getCountOsAniel($value->servico_id, $value->hora_inicio, $value->hora_fim),
                    'status' => $value->status,
                ];
            } else {
                $response['capacity'][$value->service->titulo][$period] = [
                    'start' => $value->hora_inicio,
                    'end' => $value->hora_fim,
                    'capacity' => $value->capacidade,
                    'used' => $this->getCountOsAniel($value->servico_id, $value->hora_inicio, $value->hora_fim),
                    'status' => $value->status,
                ];
            }
        }

        return $response;

    }

    private function getCountOsAniel($service, $start, $end)
    {
        $subServices = (new SubService())->whereServicoId($service)->get('titulo');
        $extract = [
            'count' => 0,
            'extract' => []
        ];

        foreach ($this->dataAniel as $key => $value) {

            foreach($subServices as $k => $v) {
                if(
                    ($value->Hora_do_Agendamento >= $start && $value->Hora_do_Agendamento <= $end) &&
                    trim($value->TIPO_SERVICO_ANIEL) == trim(mb_convert_case($v->titulo, MB_CASE_LOWER, 'UTF-8'))
                ) {
                    $extract['count']++;
                    $extract['extract'][] = $value;
                }
            }

        }


        return $extract;


    }


    public function getCalendar()
    {

        $today = Carbon::today();
        $endOfYear = Carbon::createFromDate($today->year, 12, 31);

        $dateArray = [];
        $currentDate = $today;

        while ($currentDate->lte($endOfYear)) {
            $month = mb_convert_case($currentDate->locale('pt_BR')->isoFormat('MMMM'), MB_CASE_TITLE, 'utf8');
            $dateArray[$month][] = [
                'day' => $currentDate->day,
                'name' => mb_convert_case($currentDate->locale('pt_BR')->isoFormat('ddd'), MB_CASE_TITLE, 'utf8'),
                'extense' => $currentDate->toDateString(),
            ];
            $currentDate->addDay();
        }

        return $dateArray;

    }

    private function buildingCapacity()
    {

    }
}
