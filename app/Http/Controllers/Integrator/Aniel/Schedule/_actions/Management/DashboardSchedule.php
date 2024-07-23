<?php

namespace App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Management;

use App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Voalle\OrderSync;
use App\Models\Integrator\Aniel\Schedule\ImportOrder;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class DashboardSchedule
{

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

        return response()->json([
            'message' => 'Ordem de serviço aprovada com sucesso!',
            'status' => 'Sucesso'
        ], 200);

    }

}
