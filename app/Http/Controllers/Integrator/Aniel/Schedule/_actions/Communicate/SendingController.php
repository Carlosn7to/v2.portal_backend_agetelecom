<?php

namespace App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Communicate;

use App\Http\Controllers\Controller;
use App\Models\Integrator\Aniel\Schedule\Communicate;
use App\Models\Integrator\Aniel\Schedule\CommunicateLog;
use App\Models\Integrator\Aniel\Schedule\CommunicateMirror;
use App\Models\Integrator\Aniel\Schedule\ImportOrder;
use App\Models\Integrator\Aniel\Schedule\Mirror;
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

    public function sendUniqueConfirm(Request $request)
    {

        $validator = \Validator::make($request->all(), [
            'protocol' => 'required', // Adicione outras regras de validação conforme necessário
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'Erro',
                'errors' => $validator->errors()
            ], 400);
        }

        $communicateMirror = CommunicateMirror::whereProtocolo($request->protocol)
                        ->whereStatusAniel(0)
                        ->whereEnvioConfirmacao(false)
                        ->whereEnvioDeslocamento(false)
                        ->first();

        if($communicateMirror) {
            $communicate = new InfoOrder();
            $dataClient = ImportOrder::whereProtocolo($communicateMirror->protocolo)->first();
            $dataClientAniel = Mirror::whereProtocolo($communicateMirror->protocolo)->first();


            $period = Carbon::parse($dataClientAniel->data_agendamento)->format('H:i') < '12:00:00' ? 'manhã' : 'tarde';


            $start = $period == 'manhã' ? '08:00' : '13:00';
            $end = $period == 'manhã' ? '12:00' : '18:00';


            $data = [
                'os_id' => $communicateMirror->os_id,
                'protocolo' => $communicateMirror->protocolo,
                'celular_1' => $communicate->sanitizeCellphone($dataClient->celular_1),
                'hora_inicio' => $start,
                'hora_fim' => $end,
                'data_agendamento' => Carbon::parse($dataClientAniel->data_agendamento)->format('d/m/Y')
            ];

            $communicate->sendConfirmation($data);
            $communicateMirror->envio_confirmacao = true;
            $communicateMirror->save();
            return response()->json(true);

        }

    }
}
