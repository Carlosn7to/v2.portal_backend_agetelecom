<?php

namespace App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Communicate;

use App\Http\Controllers\Controller;
use App\Models\Integrator\Aniel\Schedule\Communicate;
use App\Models\Integrator\Aniel\Schedule\CommunicateLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SendingController extends Controller
{
    public function updateStatusSending(Request $request)
    {
//        $info = new InfoOrder();
//
//        return $info->__invoke();

        $status = [
            'confirm' => 'confirmado',
            'attendant' => 'atendente',
            'reschedule' => 'reagendamento'
        ];

        $communicate = Communicate::whereCelularCliente($request->phone)
            ->whereDate('data_envio', '>=', Carbon::now()->subDay())
            ->whereStatusResposta('pendente')
            ->first();

        if ($communicate) {
            $communicate->status_resposta = $status[$request->response];
            $communicate->save();

            $log = new CommunicateLog();

            $log->envio_id = $communicate->id;
            $log->status_envio = 'enviado';
            $log->status_resposta = $status[$request->response];
            $log->atualizado_em = Carbon::now();
            $log->save();
        }

        return response()->json(true);
    }
}
