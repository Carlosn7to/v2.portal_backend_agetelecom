<?php

namespace App\Http\Controllers\Integrator\Aniel\Schedule;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Integrator\Aniel\Schedule\_actions\SubServicesSync;
use App\Models\Integrator\Aniel\Schedule\Capacity;
use App\Models\Integrator\Aniel\Schedule\SubService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BuilderController extends Controller
{

    public function getCapacity(Request $request)
    {

        if($request->period == null) {
            return response()->json([
                'message' => 'O período é obrigatório!',
                'status' => 'Erro'
            ], 400);
        }


        $period = Carbon::parse($request->period)->format('Y-m-d');

        $capacity = new Capacity();
        $result = $capacity
            ->where('data_inicio', '<=', $period)
            ->where('data_fim', '>=', $period)
            ->with('services')
            ->get();

        return response()->json(
            $result,
            200);

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