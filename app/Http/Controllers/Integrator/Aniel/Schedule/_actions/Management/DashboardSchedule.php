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

class DashboardSchedule
{

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
            ->where('data', $request->period)
            ->get()
            ->map(function ($order) {
                $decodedStatus = json_decode($order->status, true);
                foreach ($decodedStatus as &$statusItem) {
                    if (is_string($statusItem)) {
                        $statusItem = json_decode($statusItem, true);
                    }
                }
                $order->status_order = $decodedStatus;

                $approval = User::where('id', $order->aprovador_id)->first();

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

        return $this->storeAniel($order);
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


        $exportOrder = new ImportOrder();

        // Atualiza o status e a resposta no banco de dados
        $exportOrder->where('protocolo', $data['protocolo'])->update([
            'status' => $statusOs,
            'resposta' => json_encode($response),
            'status_id' => 15
        ]);

        $orderBroken = new OrderBroken();

        $order = $orderBroken->where('protocolo', $data['protocolo'])->first();
        if ($order) {


            // Adicione o novo status ao array
            $newStatus = StatusOrder::where('id', 15)->first()->toArray();
            $currentStatus[] = $newStatus;
            $currentStatus[] = $order->status;

            // Codifique novamente o array para JSON
            $updatedStatus = json_encode($currentStatus);

            // Atualize o registro
            $order->update([
                'status' => $updatedStatus,
                'aprovador_id' => auth('portal')->user()->id
            ]);
        }


        return response()->json([
            'message' => 'Ordem de serviço aprovada com sucesso!',
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
