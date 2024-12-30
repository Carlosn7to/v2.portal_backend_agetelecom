<?php

namespace App\Http\Controllers\Integrator\Aniel\Schedule;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Communicate\InfoOrder;
use App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Management\DashboardSchedule;
use App\Http\Controllers\Integrator\Aniel\Schedule\_actions\ScheduleCapacitySync;
use App\Http\Controllers\Integrator\Aniel\Schedule\_actions\SubServicesSync;
use App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Voalle\OrderSync;
use App\Http\Controllers\Integrator\Aniel\Schedule\_aux\CapacityAniel;
use App\Http\Controllers\Test\Portal\Aniel\API;
use App\Models\Integrator\Aniel\Schedule\Capacity;
use App\Models\Integrator\Aniel\Schedule\CapacityWeekly;
use App\Models\Integrator\Aniel\Schedule\OrderBroken;
use App\Models\Integrator\Aniel\Schedule\Service;
use App\Models\Integrator\Aniel\Schedule\StatusOrder;
use App\Models\Integrator\Aniel\Schedule\SubService;
use Carbon\Carbon;
use HarryGulliford\Firebird\Tests\Support\Models\Order;
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
        set_time_limit(2000000);

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


//        return $this->dataAniel;

//        return $this->dataAniel->whereIn('TIPO_SERVICO_ANIEL',
//        [
//            'visita para reinstalação do modem',
//            'ponto adicional',
//            'retorno garantia instalação 15 dias b2b',
//            'visita tecnica dr age',
//            'visita_técnica b2b',
//            'visita técnica',
//            'retorno garantia instalacao 15 dias',
//            'visita técnica - ponto adicional',
//            'retorno garantia reparo 30 dias b2b',
//            'visita técnica nps',
//            'visita técnica rede mesh',
//            'visita técnica retenção - rede mesh',
//            'reparo preventivo',
//            'visita técnica retenção',
//            'retorno garantia reparo 30 dias'
//        ])
////            ->where('Hora_do_Agendamento', '<', '12:00:00')
////            ->where('N_OS', '=', '1163805')
//            ->sortBy('N_OS')->pluck('N_OS')->count();
        $response = [];
        $response[$this->response['period']] = [
            'dayName' => $this->response['dayName'],
            'capacity' => []
        ];


        foreach($capacityWeekly as $key => $value) {

            $period = $value->hora_inicio < '12:00:00' ? 'manha' : 'tarde';

            $response[$this->response['period']]['capacity'][$value->service->titulo][$period] = [
                'start' => $value->hora_inicio,
                'end' => $value->hora_fim,
                'capacity' => $value->capacidade,
                'used' => $this->getCountOsAniel($this->dataAniel, $value->servico_id, $value->hora_inicio, $value->hora_fim),
                'status' => $value->status,
            ];

        }


        $syncSchedule = (new ScheduleCapacitySync())->sync($response);


        return $this->getCapacitySchedule();

    }

    private function updateAllCapacity()
    {
        set_time_limit(2000000);

        $today = Carbon::today()->toDateString();

        $capacity = (new Capacity())
            ->where('data', '>=', $today)
            ->orderBy('data')
            ->get(['data', 'dia_semana'])
            ->unique('data')
            ->toArray();


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

        $debug = [];

        foreach($capacity as $k => $capacityInfo) {


            foreach($capacityWeekly as $key => $value) {


                if($value->dia_semana == $capacityInfo['dia_semana']) {

                    if(!isset($response[$capacityInfo['data']])){
                        $response[$capacityInfo['data']] = [
                            'dayName' => $capacityInfo['dia_semana'],
                            'capacity' => []
                        ];
                    }

                    $dataAniel = (new CapacityAniel($capacityInfo['data']))->getCapacityAniel();

                    $dataAniel = $dataAniel->where('Data_do_Agendamento', $capacityInfo['data']);

                    $period = $value->hora_inicio < '12:00:00' ? 'manha' : 'tarde';

                    $response[$capacityInfo['data']]['capacity'][$value->service->titulo][$period] = [
                        'start' => $value->hora_inicio,
                        'end' => $value->hora_fim,
                        'capacity' => $value->capacidade,
                        'used' => $this->getCountOsAniel($dataAniel, $value->servico_id, $period),
                        'status' => $value->status,
                    ];


                }

            }

        }

        $syncSchedule = (new ScheduleCapacitySync())->sync($response);



    }

    private function getCountOsAniel($dataAniel, $service, $period)
    {
        $subServices = (new SubService())->whereServicoId($service)->get('titulo');
        $count = 0;

        $start = $period == 'manha' ? '00:00:00' : '12:00:00';
        $end = $period == 'manha' ? '11:59:59' : '23:59:59';

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

    public function getCalendar($year)
    {

        $yearSelected = $year ?? Carbon::today()->year;

        $startOfYear = Carbon::createFromDate($yearSelected, 1, 1); // 1º de janeiro do ano selecionado
        $endOfYear = Carbon::createFromDate($yearSelected, 12, 31);

        $dateArray = [];
        $currentDate = $startOfYear; // Começa no início do ano

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

    public function capacityReschedule(Request $request)
    {


//
//        $dash = new DashboardSchedule();
//
//        return $dash->__invoke();
////
//        $ordersBroken = OrderBroken::get();
//
//
//        foreach ($ordersBroken as $key => $order) {
//            $status = json_decode($order->status, true);
//
//            if(!isset($status[0])) {
//                $status[0] = $status;
//            }
//
//            if (is_array($status)) {
//                foreach ($status as $index => $subStatus) {
//                    if (is_string($subStatus)) {
//                        $status[$index] = json_decode($subStatus, true);
//                    }
//                }
//            } elseif (is_string($status)) {
//                $status = json_decode($status, true);
//            }
//
//
//            $newStatus = [];
//
//            $newStatus[] = $status[0];
//
//            if(isset($status[1])) {
//                $newStatus[] = $status[1];
//            }
//
//            // Atualiza o status no banco de dados
//            $order->status = json_encode($newStatus);
//            $order->save();
//
//        }
//
//
//        return true;

//        $infoOrder = new InfoOrder();
//
//        return $infoOrder->sendAlterOs();


        $typeService = $request->typeService;

        $datesAvailable = Capacity::where('data', '>=', Carbon::today()->toDateString())
            ->where('servico', $typeService)
            ->orderBy('data')
            ->orderBy('periodo')
            ->get(['data', 'dia_semana', 'periodo', 'status'])
            ->toArray();

        $response = [];
        foreach($datesAvailable as $key => $value){

            if(!isset($response[$value['data']])){
                $response[$value['data']] = [
                    'dayName' => mb_convert_case($value['dia_semana'], MB_CASE_TITLE, 'utf8'),
                    'period' => []
                ];
            }

            $response[$value['data']]['period'][$value['periodo']] = [
                'status' => $value['status']
            ];

        }

        return $response;

    }
}
