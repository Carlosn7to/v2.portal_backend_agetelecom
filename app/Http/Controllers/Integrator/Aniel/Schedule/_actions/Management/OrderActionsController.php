<?php

namespace App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Management;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Communicate\InfoOrder;
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

        $startDate = Carbon::now()->subDays(10)->startOfDay();
        $uniqueDates = \App\Models\Integrator\Aniel\Schedule\Mirror::where('data_agendamento', '>=', $startDate)
            ->get(['data_agendamento'])
            ->map(function ($item) {
                return Carbon::parse($item->data_agendamento)->toDateString();
            })
            ->unique()
            ->values();

        $ordersVoalle = ImportOrder::whereDate('data_agendamento', $uniqueDates[4])->whereProtocolo('1186968')
            ->get(['protocolo', 'tipo_servico', 'data_agendamento', 'node as localidade', 'status_id', 'cliente_id']);

        $services = Service::where('titulo', '<>', 'Sem vinculo')->with(['subServices', 'capacityWeekly'])
            ->get();

        $mirror = new Mirror();
        $dashboardFunctions = new DashboardSchedule();

        foreach($ordersVoalle as &$order) {

            $anielOrder = $dashboardFunctions->getDataUniqueOrder($order['protocolo']);
            $anielOrder = count($anielOrder) > 0 ? $anielOrder[0] : null;



            $dateScheduleAniel = null;

            if ($anielOrder) {

                $dateScheduleAniel = Carbon::parse($anielOrder->Data_do_Agendamento . ' ' . $anielOrder->Hora_do_Agendamento)->format('d/m/Y H:i:s');
                $order['status'] = $anielOrder->Status_Descritivo;
                $statusDetails = StatusOrder::where('titulo', $order['status'])->first();
            } else {
                $statusDetails = StatusOrder::where('id', $order['status_id'])->first();
            }

            if ($statusDetails) {
                $order['cor_indicativa'] = $statusDetails->cor_indicativa;
                $order['status_descricao'] = $statusDetails->titulo;
            }

            $order['data_agendamento'] = $dateScheduleAniel ?? Carbon::parse($order['data_agendamento'])->format('d/m/Y H:i:s');
            $order['localidade'] = mb_convert_case($order['localidade'], MB_CASE_TITLE, 'UTF-8');
            $order['tipo_servico'] = mb_convert_case($order['tipo_servico'], MB_CASE_TITLE, 'UTF-8');

            $brokenOrder = OrderBroken::where('protocolo', $order['protocolo'])->first();

            if ($brokenOrder) {
                $order['aprovador'] = $dashboardFunctions->getFormattedName($brokenOrder->aprovador_id);
                $order['solicitante'] = $dashboardFunctions->getFormattedName($brokenOrder->solicitante_id);
            }

            $communicationFirstConfirm = Communicate::whereDate(
                'data_envio',
                '>=',
                Carbon::createFromFormat('d/m/Y H:i:s', $order['data_agendamento'])->subDay()->format('Y-m-d')
            )->where('protocolo', $order['protocolo'])
                ->whereTemplate('confirmacao_agendamento_portal')
                ->first();

            $communicationSecondConfirm = Communicate::whereDate(
                'data_envio',
                '=',
                Carbon::createFromFormat('d/m/Y H:i:s', $order['data_agendamento'])->format('Y-m-d')
            )->where('protocolo', $order['protocolo'])
                ->whereTemplate('informar_deslocamento_os_portal')
                ->first();
            $order['confirmacao_cliente'] = $communicationFirstConfirm
                ? mb_convert_case($communicationFirstConfirm->status_resposta, MB_CASE_TITLE, 'UTF-8')
                : '';

            $order['confirmacao_deslocamento'] = $communicationSecondConfirm
                ? mb_convert_case($communicationSecondConfirm->status_resposta, MB_CASE_TITLE, 'UTF-8')
                : '';

            $order['servico'] = ' ';

            foreach($services as $key => $service) {
                foreach($service['subServices'] as $k => $v) {
                    $subServiceTitle = mb_convert_case($v->titulo, MB_CASE_LOWER, 'UTF-8');
                    $serviceTitle = mb_convert_case($service->titulo, MB_CASE_LOWER, 'UTF-8');

                    if ($subServiceTitle == mb_convert_case($order['tipo_servico'], MB_CASE_LOWER, 'UTF-8')) {
                        $order['servico'] = mb_convert_case($serviceTitle, MB_CASE_TITLE, 'UTF-8');
                        break;
                    }

                }
            }
        }



        foreach($ordersVoalle as $order) {
            $mirror->updateOrCreate(
                ['protocolo' => $order['protocolo']],
                [
                    'cliente_id' => $order['cliente_id'],
                    'protocolo' => $order['protocolo'],
                    'servico' => $order['servico'],
                    'sub_servico' => $order['tipo_servico'],
                    'data_agendamento' => Carbon::createFromFormat('d/m/Y H:i:s', $order['data_agendamento'])->format('Y-m-d H:i:s'),
                    'localidade' => $order['localidade'],
                    'status' => $order['status'] ?? $order['status_descricao'],
                    'cor_indicativa' => $order['cor_indicativa'] ?? '#ccc',
                    'confirmacao_cliente' => $order['confirmacao_cliente'],
                    'confirmacao_deslocamento' => $order['confirmacao_deslocamento'],
                    'solicitante' => $order['solicitante'] ?? '',
                    'aprovador' => $order['aprovador'] ?? ''
                ]
            );
        }

        dd('break');

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
