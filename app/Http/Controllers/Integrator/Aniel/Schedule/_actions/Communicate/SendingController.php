<?php

namespace App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Communicate;

use App\Http\Controllers\Controller;
use App\Models\Integrator\Aniel\Schedule\Communicate;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SendingController extends Controller
{
    public function updateStatusSending(Request $request)
    {
//        $info = new InfoOrder();
//
//        $data = [
//          'os_id' => 5234,
//            'protocolo' => '1153840',
//            'celular_1' => '5561984700440',
//        ];
//
//        return $info->sendAlterOs($data);

        $status = [
          'confirm' => 'confirmado',
          'attendant' => 'atendente',
          'reschedule' => 'reagendamento'
        ];

        $communicate = new Communicate();

        $communicate->whereCelularCliente($request->phone)
            ->whereDate('data_envio', '>=', Carbon::now()->subDay())
            ->whereStatusResposta('pendente')
            ->update([
            'status_resposta' => $status[$request->response]
        ]);

    }
}