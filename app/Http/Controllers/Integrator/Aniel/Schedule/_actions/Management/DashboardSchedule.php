<?php

namespace App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Management;

use App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Voalle\OrderSync;
use App\Http\Controllers\Integrator\Aniel\Schedule\_aux\CapacityAniel;
use App\Models\Integrator\Aniel\Schedule\Communicate;
use App\Models\Integrator\Aniel\Schedule\ImportOrder;
use App\Models\Integrator\Aniel\Schedule\Mirror;
use App\Models\Integrator\Aniel\Schedule\OrderBroken;
use App\Models\Integrator\Aniel\Schedule\Service;
use App\Models\Integrator\Aniel\Schedule\StatusOrder;
use App\Models\Portal\User\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DashboardSchedule
{
    private $typeCommand;

    public function __invoke()
    {
        set_time_limit(2000000);

        $this->mountDashboard();
        $this->mountDashboardOperational();
    }

    public function getDashboard(Request $request)
    {
        set_time_limit(2000000);


        $validator = \Validator::make($request->all(), [
            'period' => 'required|date', // Adicione outras regras de validação conforme necessário
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'Erro',
                'errors' => $validator->errors()
            ], 400);
        }


        $orderBroken = new OrderBroken();

        $orders = $orderBroken
            ->where('data', $request->period)
            ->get()
            ->map(function ($order) {
                $decodedStatus = json_decode($order->status, true);

                $order->status_order = $decodedStatus;

                $approval = User::where('id', $order->aprovador_id)->first();

                $order->periodo = mb_convert_case($order->periodo, MB_CASE_TITLE, 'UTF-8');

                $order->aprovador = $approval ? $approval->nome : '';

                $order->servico = mb_convert_case($order->servico, MB_CASE_TITLE, 'UTF-8');

                if (isset($order->aberta_por)) {
                    $words = explode(' ', $order->aberta_por);
                    // Filtrar palavras com menos de 3 letras
                    $filteredWords = array_filter($words, function ($word) {
                        return strlen($word) >= 3;
                    });
                    // Pegar as duas primeiras palavras
                    $filteredWords = array_slice($filteredWords, 0, 2);
                    // Capitalizar a primeira letra de cada palavra
                    $filteredWords = array_map('ucfirst', $filteredWords);
                    // Unir as palavras novamente
                    $order->aberta_por = implode(' ', $filteredWords);
                }

                if (isset($order->aprovador)) {
                    $words = explode(' ', $order->aprovador);
                    // Filtrar palavras com menos de 3 letras
                    $filteredWords = array_filter($words, function ($word) {
                        return strlen($word) >= 3;
                    });
                    // Pegar as duas primeiras palavras
                    $filteredWords = array_slice($filteredWords, 0, 2);
                    // Capitalizar a primeira letra de cada palavra
                    $filteredWords = array_map('ucfirst', $filteredWords);
                    // Unir as palavras novamente
                    $order->aprovador = implode(' ', $filteredWords);
                }

                unset($order->status);
                return $order;
            });

        return $orders;



    }

    public function getDashboardOperational(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'period' => 'required|date', // Adicione outras regras de validação conforme necessário
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'Erro',
                'errors' => $validator->errors()
            ], 400);
        }


        $dashboard = Mirror::whereDate('data_agendamento', $request->period)
            ->get();


        if($dashboard->isEmpty()) {
            $this->mountDashboardOperational($request->period);
            $dashboard = Mirror::whereDate('data_agendamento', $request->period)
                ->get();
        }


        return response()->json(['dashboard' => $dashboard, 'permissions' => $this->mountPermissions()]);
    }

    public function approvalOrder(Request $request)
    {
        set_time_limit(2000000);

        if($request->order == null) {
            return response()->json([
                'message' => 'O Nº da OS é obrigatório!',
                'status' => 'Erro'
            ], 400);
        }


        $order = ImportOrder::where('protocolo', $request->order)->first()->toArray();


        if(!$order) {
            return response()->json([
                'message' => 'Ordem de serviço não encontrada!',
                'status' => 'Erro'
            ], 400);
        }

        $this->typeCommand = 'approval';
        return $this->storeAniel($order);
    }

    public function rescheduleOrder(Request $request)
    {
        set_time_limit(2000000);

//        // Validação inicial dos campos
//        $validator = Validator::make($request->all(), [
//            'order' => 'required',
//            'dateHour' => 'required|date_format:Y-m-d H:i:s'
//        ], [
//            'order.required' => 'O Nº da OS é obrigatório!',
//            'dateHour.required' => 'A data e hora são obrigatórias!',
//            'dateHour.date_format' => 'A data e hora devem estar no formato válido (YYYY-MM-DD HH:MM:SS)!'
//        ]);
//
//        if ($validator->fails()) {
//            return response()->json([
//                'message' => 'Erro de validação',
//                'errors' => $validator->errors(),
//                'status' => 'Erro'
//            ], 400);
//        }

        // Validação adicional da data e hora com Carbon
        try {
//            $validateDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->dateHour);

            $date = $request->date;
            $period = $request->period;


            $order = ImportOrder::where('protocolo', $request->order)->first()->toArray();

            if(!$order) {
                return response()->json([
                    'message' => 'Ordem de serviço não encontrada!',
                    'status' => 'Erro'
                ], 400);
            }


            if($period == 'manha') {
                $dateHour = Carbon::parse($date . ' 09:00:00');
            } else {
                $dateHour = Carbon::parse($date . ' 13:00:00');
            }



            $order['data_agendamento'] = Carbon::parse($dateHour)->format('Y-m-d H:i:s');

            $this->typeCommand = 'reschedule';

            return $this->storeAniel($order);



        } catch (\Exception $e) {
            return response()->json([
                'message' => 'A data e hora devem estar no formato válido (YYYY-MM-DD HH:MM:SS)!',
                'status' => 'Erro'
            ], 400);
        }

    }

    private function updateStatus($order, $response, $statusOs)
    {
        $status = [
            'approval' => 15,
            'reschedule' => 16
        ];

        $exportOrder = new ImportOrder();

        // Atualiza o status e a resposta no banco de dados
        $exportOrder->where('protocolo', $order['protocolo'])->update([
            'status' => $statusOs,
            'resposta' => json_encode($response),
            'status_id' => $status[$this->typeCommand],
            'data_agendamento' => $order['data_agendamento']
        ]);

        $orderBroken = new OrderBroken();

        $getOrderBroken = $orderBroken->where('protocolo', $order['protocolo'])->first();
        if ($getOrderBroken) {
            // Adicione o novo status ao array
            $newStatus = StatusOrder::where('id', $status[$this->typeCommand])->first()->toArray();

            $newStatus['created_at'] = Carbon::now()->format('Y-m-d H:i:s');

            $currentStatus[] = $newStatus;
            $currentStatus[] = json_decode($getOrderBroken->status, true)[0];

            // Codifique novamente o array para JSON
            $updatedStatus = json_encode($currentStatus);

            // Atualize o registro
            $getOrderBroken->update([
                'status' => $updatedStatus,
                'aprovador_id' => auth('portal')->user()->id
            ]);
        }

    }

    private function storeAniel($data)
    {
        $client = new Client();

        $form = [
            "cpf" => $data['cliente_documento'],
            "tipoServico" => $data['tipo_servico'],
            "subTipoServico" => $data['tipo_servico'],
            "projeto" => "CASA CLIENTE",
            "codCt" => "OP01",
            "numOS" => $data['protocolo'],
            "dataHoraAgendamento" => $data['data_agendamento'] != null ? Carbon::parse($data['data_agendamento'])->format('Y-m-d\TH:i:s.v\Z') : '',
            "tipoImovel" => "INDIFERENTE",
            "grupoArea" => $data['grupo'],
            "area" => $data['area_despacho'],
            "localidade" => $data['node'],
            "endereco" => $data['endereco'],
            "numeroEndereco" => $data['numero'],
            "cep" => $data['cep'],
            "complemento" => $data['complemento'],
            "bairro" => $data['bairro'],
            "cidade" => $data['cidade'],
            "pontoReferencia" => "",
            "uf" => "DF",
            "observacao" => $data['observacao'],
            "latitude" => $data['latitude'],
            "longitude" => $data['longitude'],
            "tecnico" => "",
            "nomeCliente" => $data['cliente_nome'],
            "telefoneCelularCliente" => $data['celular_1'],
            "telefoneFixoCliente" => $data['celular_2'],
            "emailCliente" => $data['email'],
            "contratoCliente" => $data['contrato_id'],
            "numDoc" => $data['protocolo'],
            "settings" => [
                "user" => config('services.aniel.user'),
                "password" => config('services.aniel.password'),
                "token" => config('services.aniel.token')
            ]
        ];



        $client = $client->post('https://cliente01.sinapseinformatica.com.br:4383/AGE/Servicos/API_Aniel/api/OsApiController/CriarOrdemServico', [
            'json' => $form
        ]);


        $response = json_decode($client->getBody()->getContents());
        $status = $response->ok;

        // Define o status da ordem de serviço baseado na resposta e no status atual
        $statusOs = (!$status && $response->mensagem == 'Erro: Ordem de servico ja cadastrada')
            ? 'IMPORTADA'  // Se o status for falso e a mensagem for 'Erro: Ordem de servico ja cadastrada', define como 'IMPORTADA'
            : ($status ? 'IMPORTADA' : 'ERRO');  // Caso contrário, se o status for verdadeiro, define como 'IMPORTADA', senão, define como 'ERRO'


        $this->updateStatus($data, $response, $statusOs);

        return response()->json([
            'message' => 'Ordem de serviço alterada com sucesso!',
            'status' => 'Sucesso'
        ], 200);

    }

    private function mountDashboard()
    {

        $services = Service::where('titulo', '<>', 'Sem vinculo')->with(['subServices', 'capacityWeekly'])
            ->get();

        $orders = ImportOrder::where('status', 'Pendente')
            ->where('status_id', '<>', 1)
            ->with('statusOrder')
            ->get(['id', 'protocolo', 'data_agendamento', 'tipo_servico', 'status_id', 'criado_por', 'setor', 'area_despacho']);


        $orderBroken = new OrderBroken();


        foreach ($orders->toArray() as &$order) {
            $typeSubService = mb_convert_case($order['tipo_servico'], MB_CASE_LOWER, 'UTF-8');
            $typeService = null;
            $dateTime = Carbon::parse($order['data_agendamento']);
            $date = $dateTime->toDateString();
            $period = $dateTime->hour < 12 ? 'manha' : 'tarde';
            $order['periodo'] = $period;

            foreach ($services as $key => $service) {

                foreach($service['subServices'] as $k => $v) {
                    $subServiceTitle = mb_convert_case($v->titulo, MB_CASE_LOWER, 'UTF-8');
                    $serviceTitle = mb_convert_case($service->titulo, MB_CASE_LOWER, 'UTF-8');
                    if ($subServiceTitle == $typeSubService) {
                        $typeService = $serviceTitle;
                        break;
                    }

                }

            }
            $order['servico'] = $typeService;
            $order['status_order']['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
            $orderBroken->firstOrCreate(
                ['os_id' => $order['id']],
                [
               'os_id' => $order['id'],
               'data' => $date,
               'protocolo' => $order['protocolo'],
               'servico' => $order['servico'],
               'subservico' => $order['tipo_servico'],
               'hora_agendamento' => $dateTime->format('H:i:s'),
               'periodo' => $period,
               'status' => json_encode([$order['status_order']]),
               'localidade' => $order['area_despacho'],
               'aberta_por' => $order['criado_por'],
               'setor' => $order['setor']
           ]);


        }


    }

    private function mountDashboardOperational($period = null)
    {

        $startDate = Carbon::now()->subDays(5)->startOfDay();
        $uniqueDates = Mirror::where('data_agendamento', '>=', $startDate)
            ->get(['data_agendamento'])
            ->map(function ($item) {
                return Carbon::parse($item->data_agendamento)->toDateString();
            })
            ->unique()
            ->values();

        if($period) {
            $ordersVoalle = ImportOrder::whereDate('data_agendamento', $period)
                ->get(['protocolo', 'tipo_servico', 'data_agendamento', 'node as localidade', 'status_id', 'cliente_id']);
        } else {
            $ordersVoalle = ImportOrder::whereIn(\DB::raw('DATE(data_agendamento)'), $uniqueDates)
                ->get(['protocolo', 'tipo_servico', 'data_agendamento', 'node as localidade', 'status_id', 'cliente_id']);
        }

        $services = Service::where('titulo', '<>', 'Sem vinculo')->with(['subServices', 'capacityWeekly'])
            ->get();


        $ordersVoalle->each(function ($order) use($services) {

            $order->protocolo = (string)$order->protocolo;
            $anielOrder = $this->getDataUniqueOrder($order->protocolo);
            $anielOrder = count($anielOrder) > 0 ? $anielOrder[0] : null;

            $dateScheduleAniel = null;

            if ($anielOrder) {
                $dateScheduleAniel = Carbon::parse($anielOrder->Data_do_Agendamento . ' ' . $anielOrder->Hora_do_Agendamento)->format('d/m/Y H:i:s');
                $order->status = $anielOrder->Status_Descritivo;
                $statusDetails = StatusOrder::where('titulo', $order->status)->first();
            } else {
                $statusDetails = StatusOrder::where('id', $order->status_id)->first();
            }

            if ($statusDetails) {
                $order->cor_indicativa = $statusDetails->cor_indicativa;
                $order->status_descricao = $statusDetails->titulo;
            }

            $order->data_agendamento = $dateScheduleAniel ?? Carbon::parse($order->data_agendamento)->format('d/m/Y H:i:s');
            $order->localidade = mb_convert_case($order->localidade, MB_CASE_TITLE, 'UTF-8');
            $order->tipo_servico = mb_convert_case($order->tipo_servico, MB_CASE_TITLE, 'UTF-8');

            $brokenOrder = OrderBroken::where('protocolo', $order->protocolo)->first();

            if ($brokenOrder) {
                $order->aprovador = $this->getFormattedName($brokenOrder->aprovador_id);
                $order->solicitante = $this->getFormattedName($brokenOrder->solicitante_id);
            }

            $communication = Communicate::where('protocolo', $order->protocolo)
                ->first();

            $order->comunicacao = $communication
                ? mb_convert_case($communication->status_resposta, MB_CASE_TITLE, 'UTF-8')
                : null;

            $order->servico = ' ';



            foreach($services as $key => $service) {
                foreach($service['subServices'] as $k => $v) {
                    $subServiceTitle = mb_convert_case($v->titulo, MB_CASE_LOWER, 'UTF-8');
                    $serviceTitle = mb_convert_case($service->titulo, MB_CASE_LOWER, 'UTF-8');

                    if ($subServiceTitle == mb_convert_case($order->tipo_servico, MB_CASE_LOWER, 'UTF-8')) {
                        $order->servico = mb_convert_case($serviceTitle, MB_CASE_TITLE, 'UTF-8');
                        break;
                    }

                }
            }


            return $order;
        });

        $mirror = new Mirror();


        foreach($ordersVoalle as $order) {
            $mirror->updateOrCreate(
                ['protocolo' => $order->protocolo],
                [
                    'cliente_id' => $order->cliente_id,
                    'protocolo' => $order->protocolo,
                    'servico' => $order->servico,
                    'sub_servico' => $order->tipo_servico,
                    'data_agendamento' => Carbon::createFromFormat('d/m/Y H:i:s', $order->data_agendamento)->format('Y-m-d H:i:s'),
                    'localidade' => $order->localidade,
                    'status' => $order->status ?? $order->status_descricao,
                    'cor_indicativa' => $order->cor_indicativa,
                    'confirmacao_cliente' => $order->comunicacao,
                    'solicitante' => $order->solicitante ?? '',
                    'aprovador' => $order->aprovador ?? ''
                ]
            );
        }

    }

    public function getDataUniqueOrder($protocol)
    {
        return \DB::connection('aniel')->select($this->getQueryUniqueOrder($protocol));
    }

    private function getQueryUniqueOrder($protocol)
    {

        return <<<SQL
            SELECT DISTINCT
                dp.NUM_OBRA_ORIGINAL AS "N_OS",
                dp.NUM_OBRA,
                dp.PROJETO,
                dp.COD_TIPO_SERV,
                LOWER(tse.descricao) AS "TIPO_SERVICO_ANIEL",
                c.RAZAO_SOCIAL AS "Nome Cliente",
                CASE WHEN e.NOME IS NULL THEN 'SEM TÉCNICO ATRIBUIDO'
                     ELSE e.NOME END AS "Nome Tecnico",
                dp.DIA AS "Criacao",
                ts.DESCRICAO AS "Turno",
                dp.DATA_MAXIMA AS "Data_do_Agendamento",
                dp.HORA_MAXIMA AS "Hora_do_Agendamento",
                dp.STATUS_EXECUCAO as "Status",
                TRIM(
                    CASE
                        WHEN dp.STATUS_EXECUCAO = 0 THEN 'Aberta Aguardando Atendimento'
                        WHEN dp.STATUS_EXECUCAO = 1 THEN 'Fechada Improdutiva'
                        WHEN dp.STATUS_EXECUCAO = 2 THEN 'Fechada Produtiva'
                        WHEN dp.STATUS_EXECUCAO = 3 THEN 'Atendimento Iniciado'
                        WHEN dp.STATUS_EXECUCAO = 4 THEN 'Aberta Aguardando Agendamento'
                        WHEN dp.STATUS_EXECUCAO = 6 THEN 'OS em Deslocamento'
                        WHEN dp.STATUS_EXECUCAO = 9 THEN 'Cancelada'
                        WHEN dp.STATUS_EXECUCAO = 10 THEN 'Paralisado'
                        WHEN dp.STATUS_EXECUCAO = 11 THEN 'Atendimento Reiniciado'
                        WHEN dp.STATUS_EXECUCAO = 13 THEN 'Aberta Aguardando Responsável'
                        ELSE 'Status Desconhecido'
                    END
                ) AS "Status_Descritivo"
            FROM TB_DOCUMENTO_PRODUCAO dp
            LEFT JOIN TB_CLIENTE c ON c.RAZAO_SOCIAL = dp.TITULAR
            LEFT JOIN TB_EQUIPE e ON e.EQUIPE = dp.EQUIPE
            LEFT JOIN TB_TURNO_SERVICO ts ON ts.ID = dp.COD_TURNO_SERV
            LEFT JOIN TB_TIPO_SERVICO_EQUIPE tse ON tse.COD_TIPO_SERV = dp.COD_TIPO_SERV
            JOIN (
                SELECT
                    NUM_OBRA_ORIGINAL,
                    MAX(NUM_OBRA) AS max_num_obra
                FROM TB_DOCUMENTO_PRODUCAO
                GROUP BY NUM_OBRA_ORIGINAL
            ) sub ON dp.NUM_OBRA_ORIGINAL = sub.NUM_OBRA_ORIGINAL
                  AND dp.NUM_OBRA = sub.max_num_obra
            WHERE dp.NUM_OBRA_ORIGINAL = '{$protocol}'
            ORDER BY dp.NUM_OBRA DESC;
        SQL;


//        return <<<SQL
//            SELECT tdp.NUM_OBRA, tdp.NUM_OBRA_ORIGINAL, tdp.STATUS_EXECUCAO,
//                   tdp.DIA,
//                   tdp.DATA_MAXIMA
//            FROM TB_DOCUMENTO_PRODUCAO tdp
//            where tdp.NUM_OBRA_ORIGINAL = '1166450'
//        SQL;
//
//        return <<<SQL
//            SELECT DISTINCT
//                dp.NUM_OBRA_ORIGINAL AS "N_OS"
//            FROM TB_DOCUMENTO_PRODUCAO dp
//            WHERE dp.DATA_MAXIMA = '{$this->period}'
//        SQL;


    }

    private function getFormattedName($userId) {
        if ($userId) {
            $user = User::find($userId, ['nome']);
            if ($user) {
                $words = explode(' ', $user->nome);
                $filteredWords = array_filter($words, fn($word) => strlen($word) >= 3);
                $filteredWords = array_slice($filteredWords, 0, 2);
                $filteredWords = array_map('ucfirst', $filteredWords);
                return implode(' ', $filteredWords);
            }
        }
        return null;
    }

    private function mountPermissions()
    {
        $approval = [
            'eline.paulo',
            'luciene.silva',
            'ralmarley.menezes',
            'thaina.araujo',
            'vicktoria.motta',
            'juliane.araujo',
            'barbara.siqueira',
            'camila.pereira',
            'mauro.diogo',
            'abdre.guilherme',
            'michelly.pinheiro',
            'carlos.neto'
        ];

        $preApproval = [
            'betania.ferreira',
//            'carlos.neto',
            'larissa.tavares',
            'larissa.soares',
            'denise.araujo',
            'victor.bezerra',
            'matheus.fagundes',
            'lais.pontes',
            'thaina.silva',
            'jheeferson.almeida'
        ];

        $permissions = [
            'approval' => false,
            'preApproval' => false,
            'reschedule' => true
        ];


        $login = auth('portal')->user()->login;


        if(in_array($login, $approval)) {
            $permissions['approval'] = true;
        }

        if(in_array($login, $preApproval)) {
            $permissions['pre-approval'] = true;
        }



        return $permissions;

    }

}
