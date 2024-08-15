<?php

namespace App\Console\Commands\Integrator\Schedule;

use App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Communicate\InfoOrder;
use App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Management\DashboardSchedule;
use App\Jobs\UpdateMirrorAniel;
use App\Models\Integrator\Aniel\Schedule\Communicate;
use App\Models\Integrator\Aniel\Schedule\ImportOrder;
use App\Models\Integrator\Aniel\Schedule\OrderBroken;
use App\Models\Integrator\Aniel\Schedule\Service;
use App\Models\Integrator\Aniel\Schedule\StatusOrder;
use Carbon\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Console\Command;

class Mirror extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aniel:mirror';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Espelhamento das ordens de serviço do Aniel';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $info = new InfoOrder();

        $info->__invoke();

        $startDate = Carbon::now()->subDays(10)->startOfDay();
        $uniqueDates = \App\Models\Integrator\Aniel\Schedule\Mirror::where('data_agendamento', '>=', $startDate)
            ->get(['data_agendamento'])
            ->map(function ($item) {
                return Carbon::parse($item->data_agendamento)->toDateString();
            })
            ->unique()
            ->values();


        $services = Service::where('titulo', '<>', 'Sem vinculo')->with(['subServices', 'capacityWeekly'])
            ->get();

        foreach ($uniqueDates as $date) {

            $ordersVoalle = ImportOrder::whereDate('data_agendamento', $date)
                ->get(['protocolo', 'tipo_servico', 'data_agendamento', 'node as localidade', 'status_id', 'cliente_id'])->toArray();


            $mirror = new \App\Models\Integrator\Aniel\Schedule\Mirror();
            $dashboardFunctions = new DashboardSchedule();

            foreach($ordersVoalle as &$order) {

                $anielOrder = $dashboardFunctions->getDataUniqueOrder($order['protocolo']);
                $anielOrder = count($anielOrder) > 0 ? $anielOrder[0] : null;

                $dateScheduleAniel = null;

                if ($anielOrder) {

                    $dateScheduleAniel = Carbon::parse($anielOrder->Data_do_Agendamento . ' ' . $anielOrder->Hora_do_Agendamento)->format('d/m/Y H:i:s');
                    $order['status'] = $anielOrder->Status_Descritivo;
                    $statusDetails = StatusOrder::where('titulo', $order['status'])->first();
                    $order['responsavel'] = mb_convert_case($anielOrder->Nome_Tecnico, MB_CASE_TITLE, 'UTF-8');
                } else {
                    $statusDetails = StatusOrder::where('id', $order['status_id'])->first();
                    $order['responsavel'] = '';
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
                        'aprovador' => $order['aprovador'] ?? '',
                        'responsavel' => $order['responsavel'] ?? ''
                    ]
                );
            }

        }



    }
}
