<?php

namespace App\Http\Controllers\Integrator\Aniel\Schedule;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Integrator\Aniel\Schedule\_actions\SubServicesSync;
use App\Http\Controllers\Integrator\Aniel\Schedule\_aux\CapacityAniel;
use App\Http\Controllers\Test\Portal\Aniel\API;
use App\Models\Integrator\Aniel\Schedule\Capacity;
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



        $period = Carbon::parse($request->period)->format('Y-m-d');

        $this->dataAniel = (new CapacityAniel($period))->getCapacityAniel();


        $services = Service::where('titulo','!=', 'Sem vinculo')->with(['subServices', 'capacity'])->get(['id', 'titulo', 'segmento'])->toArray();

        $anielCountOs = $this->getCountOsAniel($services);

        return $anielCountOs;

        $response = [];

        foreach($services as $key => $service) {

            foreach($service['capacity'] as $kk => $value) {

                $response[$service['segmento']][$value['periodo']][] = [
                    'titulo' => $service['titulo'],
                    'capacidade' => $value['capacidade'],
                    'data_inicio' => $value['data_inicio'],
                    'data_fim' => $value['data_fim'],
                ];

            }

        }


        return response()->json(
            $response,
            200);

    }

    private function getCountOsAniel($services)
    {
        $result = [];

        dd($services);




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
