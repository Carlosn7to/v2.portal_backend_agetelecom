<?php

namespace App\Http\Controllers\Integrator\Aniel\Schedule;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Integrator\Aniel\Schedule\_actions\ScheduleCapacitySync;
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
    private $response;

    public function __invoke()
    {
        return $this->updateAllCapacity();
    }

    public function getCapacity(Request $request)
    {

        if($request->period == null) {
            return response()->json([
                'message' => 'O período é obrigatório!',
                'status' => 'Erro'
            ], 400);
        }

        $this->response = [
            'period' => Carbon::parse($request->period)->format('Y-m-d'),
            'dayName' => Carbon::parse($request->period)->locale('pt_BR')->isoFormat('dddd'),
            'capacity' => []
        ];

        return $this->getCapacitySchedule() == null ? $this->updateCapacity() : $this->getCapacitySchedule();

    }

    private function getCapacitySchedule()
    {
        $capacities = Capacity::where('data', $this->response['period'])
            ->where('dia_semana', $this->response['dayName'])
            ->get();

        if($capacities->count() > 0) {
            $groupedCapacities = [
                'manha' => [],
                'tarde' => []
            ];

            foreach ($capacities as $capacity) {
                if ($capacity->periodo === 'manha') {
                    $groupedCapacities['manha'][] = $capacity;
                } elseif ($capacity->periodo === 'tarde') {
                    $groupedCapacities['tarde'][] = $capacity;
                }
            }

            return $groupedCapacities;
        }

        return null;



    }

    private function updateCapacity()
    {

        $capacityWeekly = (new CapacityWeekly())
            ->where('dia_semana', $this->response['dayName'])
            ->whereNull('data_final')
            ->with('service')
            ->get();


        $this->dataAniel = (new CapacityAniel($this->response['period']))->getCapacityAniel();

        $response = [];
        $response['period'] = $this->response['period'];
        $response['dayName'] = $this->response['dayName'];

        foreach($capacityWeekly as $key => $value) {

            $period = $value->hora_inicio < '12:00:00' ? 'manha' : 'tarde';


            $response['capacity'][$value->service->titulo][$period] = [
                'start' => $value->hora_inicio,
                'end' => $value->hora_fim,
                'capacity' => $value->capacidade,
                'used' => $this->getCountOsAniel($this->dataAniel, $value->servico_id, $value->hora_inicio, $value->hora_fim),
                'status' => $value->status,
            ];
        }


        $syncSchedule = (new ScheduleCapacitySync([$response]))->sync();


        return $this->getCapacitySchedule();

    }

    private function updateAllCapacity()
    {
        $capacity = (new Capacity())->where('data', '>=', Carbon::today()->toDateString())
            ->get(['data', 'dia_semana'])->unique('data')->toArray();



        $dataCapacityWeekly = [];

        foreach($capacity as $key => $value) {
            $dataCapacityWeekly[] = $value['dia_semana'];
        }



        $capacityWeekly = (new CapacityWeekly())
            ->whereIn('dia_semana', $dataCapacityWeekly)
            ->whereNull('data_final')
            ->with('service')
            ->get();


        $response = [];


        foreach($capacity as $k => $capacityInfo) {



            foreach($capacityWeekly as $key => $value) {


                if($value->dia_semana == $capacityInfo['dia_semana']) {
                    $response['period'] = $capacityInfo['data'];
                    $response['dayName'] = $capacityInfo['dia_semana'];

                    $dataAniel = (new CapacityAniel($response['period']))->getCapacityAniel();

                    $period = $value->hora_inicio < '12:00:00' ? 'manha' : 'tarde';

                    $response['capacity'][$value->service->titulo][$period] = [
                        'start' => $value->hora_inicio,
                        'end' => $value->hora_fim,
                        'capacity' => $value->capacidade,
                        'used' => $this->getCountOsAniel($dataAniel, $value->servico_id, $value->hora_inicio, $value->hora_fim),
                        'status' => $value->status,
                    ];

                    $syncSchedule = (new ScheduleCapacitySync())->sync([$response]);
                }




            }
        }


    }

    private function getCountOsAniel($dataAniel, $service, $start, $end)
    {
        $subServices = (new SubService())->whereServicoId($service)->get('titulo');
        $count = 0;

        foreach ($dataAniel as $key => $value) {

            foreach($subServices as $k => $v) {
                if(
                    ($value->Hora_do_Agendamento >= $start && $value->Hora_do_Agendamento <= $end) &&
                    trim($value->TIPO_SERVICO_ANIEL) == trim(mb_convert_case($v->titulo, MB_CASE_LOWER, 'UTF-8'))
                ) {
                    $count++;
                }
            }

        }


        return $count;


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

}
