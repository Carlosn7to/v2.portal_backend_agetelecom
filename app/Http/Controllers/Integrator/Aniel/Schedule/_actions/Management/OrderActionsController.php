<?php

namespace App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Management;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Communicate\InfoOrder;
use App\Http\Controllers\Integrator\Aniel\Schedule\_automations\ClearTechnical;
use App\Jobs\UpdateMirrorAniel;
use App\Models\Integrator\Aniel\Schedule\Communicate;
use App\Models\Integrator\Aniel\Schedule\CommunicateMirror;
use App\Models\Integrator\Aniel\Schedule\ImportOrder;
use App\Models\Integrator\Aniel\Schedule\Mirror;
use App\Models\Integrator\Aniel\Schedule\OrderBroken;
use App\Models\Integrator\Aniel\Schedule\Service;
use App\Models\Integrator\Aniel\Schedule\StatusOrder;
use Carbon\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use PHPUnit\Event\Telemetry\Info;
use Tests\Browser\Integrator\Aniel\AlterSchedule;

class OrderActionsController extends Controller
{

    public function getDataOrder(Request $request)
    {

        $dataImport = ImportOrder::where('protocolo', $request->protocol)
                    ->first(['cliente_nome', 'protocolo', 'celular_1', 'email',
                        'contrato_id', 'endereco', 'numero', 'area_despacho', 'cidade'
                        ]);


        $dataAniel = Mirror::where('protocolo', $request->protocol)
                    ->first(['protocolo', 'status', 'cor_indicativa', 'data_agendamento', 'servico', 'sub_servico', 'confirmacao_cliente', 'confirmacao_deslocamento', 'localidade'
                        ]);


        $response = array_merge($dataImport->toArray(), $dataAniel->toArray());

        $response['cliente_nome'] = mb_convert_case($response['cliente_nome'], MB_CASE_TITLE, 'UTF-8');
        $sanitize = new InfoOrder();

        $response['celular_1'] = $sanitize->sanitizeCellphone($response['celular_1']);
        $response['celular_1'] = $this->formatNumber($response['celular_1']);

        return response()->json($response);

    }

    public function sendConfirm(Request $request)
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
            ->first();


        if($communicateMirror) {

            if($communicateMirror->envio_confirmacao || $communicateMirror->envio_deslocamento) {
                return response()->json('Comunicação já enviada', 400);
            }


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

            $mirrorOrder = Mirror::whereProtocolo($communicateMirror->protocolo)->first();
            $mirrorOrder->confirmacao_cliente = 'Pendente';
            $mirrorOrder->save();
            return response()->json(true);

        }
    }

    public function clearTechnical()
    {

        $pythonFilePath = base_path('app'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR.'Integrator'.DIRECTORY_SEPARATOR.'Aniel'.DIRECTORY_SEPARATOR.'Schedule'.DIRECTORY_SEPARATOR.'_automations'.DIRECTORY_SEPARATOR.'pyCodes'.DIRECTORY_SEPARATOR.'clearTechnical.py');

        $command = "python3 \"$pythonFilePath\"";

        $output = shell_exec($command);

        return $output;
    }

    private function formatNumber($number)
    {
        $number = preg_replace('/\D/', '', $number);

        // Verifica se o número começa com o código do país (55 para o Brasil)
        if (strlen($number) == 13 && substr($number, 0, 2) == '55') {
            $number = substr($number, 2);
        }

        // Formata o número para (61) 99999-9999 ou (61) 9999-9999 dependendo do comprimento
        if (strlen($number) == 11) {
            $formatted = sprintf('(%s) %s-%s', substr($number, 0, 2), substr($number, 2, 5), substr($number, 7, 4));
        } elseif (strlen($number) == 10) {
            $formatted = sprintf('(%s) %s-%s', substr($number, 0, 2), substr($number, 2, 4), substr($number, 6, 4));
        } else {
            // Retorna o número original se ele não tiver o comprimento esperado
            return $number;
        }

        return $formatted;

    }

}
