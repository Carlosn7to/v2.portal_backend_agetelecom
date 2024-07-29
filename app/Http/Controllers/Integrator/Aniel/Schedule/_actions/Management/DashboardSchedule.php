<?php

namespace App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Management;

use App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Voalle\OrderSync;
use App\Models\Integrator\Aniel\Schedule\ImportOrder;
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

        return $this->mountDashboard();
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
            ->where('data', '2024-07-29')
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

            $currentStatus[] = $newStatus;
            $currentStatus[] = json_decode($getOrderBroken->status, true);

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


}
