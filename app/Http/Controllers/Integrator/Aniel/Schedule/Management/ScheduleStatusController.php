<?php

namespace App\Http\Controllers\Integrator\Aniel\Schedule\Management;

use App\Http\Controllers\Controller;
use App\Models\Integrator\Aniel\Schedule\Capacity;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduleStatusController extends Controller
{
    public function getSchedules()
    {
        $schedules = Capacity::with('user')
            ->whereDate('data', '>=', Carbon::now()->format('Y-m-d'))
            ->whereDate('data', '<=', Carbon::now()->addDays(6)->format('Y-m-d'))
            ->orderBy('data')
            ->orderBy('periodo')
            ->orderBy('servico')
            ->get(['id', 'data', 'dia_semana', 'servico', 'periodo', 'capacidade', 'utilizado', 'status', 'atualizado_por', 'motivo_fechamento', 'data_fechamento', 'hora_fechamento'])
            ->groupBy('data');

        return response()->json($schedules, 200);

    }
    public function alterStatus(Request $request)
    {
        $permittedUsers = [
            'mauro.diogo',
            'andre.rocha',
            'michelly.pinheiro',
            'carlos.neto'
        ];

        $motives = [
            1 => 'Sobrecarga de Serviços em Áreas Críticas',
            2 => 'Capacidade atingida',
            3 => 'O período de agendamento expirou.'
        ];

        if (!in_array(auth('portal')->user()->login, $permittedUsers)) {
            return response()->json([
                'message' => 'Usuário não autorizado'
            ], 403);
        }

        $request->validate([
            'schedule_id' => 'required|integer',
            'motive_id' => 'required|integer',
            'command' => 'required|string'
        ]);

        $schedule = Capacity::find($request->schedule_id);



        if(!$schedule) {
            return response()->json([
                'message' => 'Não foi encontrada nenhuma projeção de agenda com o id informado'
            ], 404);
        }



        if (in_array($request->command, ['close', 'reopen'])) {
            $schedule->status = $request->command === 'close' ? 'fechada' : 'aberta';
            $schedule->motivo_fechamento = $request->command === 'close' ? $motives[$request->motive_id] : null;
            $schedule->data_fechamento = $request->command === 'close' ? Carbon::now()->format('Y-m-d') : null;
            $schedule->hora_fechamento = $request->command === 'close' ? Carbon::now()->format('H:i:s') : null;
            $schedule->atualizado_por = auth('portal')->user()->id;
            $schedule->save();

            $message = $request->command === 'close'
                ? 'Agenda fechada com sucesso!'
                : 'Agenda reaberta com sucesso!';

            return response()->json(['message' => $message], 200);
        }




    }

    public function alterCapacity(Request $request)
    {
        $permittedUsers = [
            'mauro.diogo',
            'andre.rocha',
            'michelly.pinheiro',
            'carlos.neto'
        ];

        if (!in_array(auth('portal')->user()->login, $permittedUsers)) {
            return response()->json([
                'message' => 'Usuário não autorizado'
            ], 403);
        }

        $schedule = $request->schedule;

        foreach($schedule as $key => $value) {
            $schedule = Capacity::find($value['id']);
            $schedule->capacidade = $value['nova_capacidade'];
            $schedule->atualizado_por = auth('portal')->user()->id;
            $schedule->save();
        }

        return response()->json(['message' => 'Capacidade alterada com sucesso!'], 200);

    }
}
