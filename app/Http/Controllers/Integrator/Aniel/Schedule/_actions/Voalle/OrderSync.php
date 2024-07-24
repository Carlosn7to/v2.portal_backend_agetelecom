<?php

namespace App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Voalle;

use App\Models\Integrator\Aniel\Schedule\Capacity;
use App\Models\Integrator\Aniel\Schedule\CapacityWeekly;
use App\Models\Integrator\Aniel\Schedule\ImportOrder;
use App\Models\Integrator\Aniel\Schedule\Service;
use App\Models\Integrator\Aniel\Schedule\SubService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Nette\Utils\Random;

class OrderSync
{
    private $data; // Dados da consulta no voalle
    private $idServices; // Dados dos serviços que serão consultados na tabela aniel_agenda_subservicos
    private $lastProtocol;  // Dados do último protocolo inserido na tabela aniel_agenda_importacao_ordens

    public function __invoke()
    {
        return $this->response();
    }

    public function __construct()
    {
        $idServices = $this->getIdOrders();
        $this->idServices = implode(',', array_map('intval', $idServices));
    }

    public function response()
    {
        $this->getData();
        $this->importOrder();
    }

    private function importOrder()
    {
        set_time_limit(200000000);

        $import = new ImportOrder();

        foreach($this->data as $key => $value) {
            $import->firstOrCreate(
                ['protocolo' => $value->protocol],
                [
                'atendimento_id' => $value->assignment_id,
                'protocolo' => $value->protocol,
                'contrato_id' => $value->contract_id,
                'cliente_id' => $value->client_id,
                'cliente_documento' => $value->doc,
                'cliente_nome' => $value->client_name,
                'email' => $value->email,
                'celular_1' => $value->cell_phone,
                'celular_2' => $value->cell_phone_2,
                'endereco' => $value->address,
                'numero' => $value->number,
                'complemento' => $value->complement,
                'cidade' => $value->city,
                'bairro' => $value->neighborhood,
                'cep' => $value->cep,
                'latitude' => $value->latitude,
                'longitude' => $value->longitude,
                'tipo_imovel' => $value->type_immobile,
                'tipo_servico' => $value->type_service,
                'node' => $value->Node,
                'area_despacho' => $value->dispatch_area,
                'observacao' => $value->observation,
                'grupo' => $value->group,
                'data_agendamento' => $value->schedule_date?? null,
                'status_id' => 1,
                'criado_por' => mb_convert_case($value->created_by, MB_CASE_TITLE, 'UTF-8'),
                'setor' => $value->team
            ]);
        }


        $this->importAniel();


    }

    private function importAniel()
    {
        $exportOrder = new ImportOrder();

        $orders = $exportOrder->where('status', '<>', 'IMPORTADA')
            ->get();

        $ordersValidated = $this->identifyCapacity($orders);

        $newOrdersValidated = array_filter($ordersValidated, function($order) {
            return $order['status_id'] == 1;
        });


        foreach($newOrdersValidated as $key => $data) {

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

            // Atualiza o status e a resposta no banco de dados
            $exportOrder->where('protocolo', $data['protocolo'])->update([
                'status' => $statusOs,
                'resposta' => json_encode($response),
                'status_id' => 11
            ]);



        }




    }

    private function getIdOrders()
    {
        $subServices = SubService::where('servico_id', '!=', '1')->get('voalle_id');
        $idServices = [];
        foreach($subServices as $key => $value) {
            $idServices[] = $value->voalle_id;
        }

        return $idServices;

    }

    private function identifyCapacity($orders)
    {
        $services = Service::where('titulo', '<>', 'Sem vinculo')->with(['subServices', 'capacityWeekly'])
            ->get();

        $capacity = new Capacity();

        $orderUpdate = new ImportOrder();

        $grouped = [];

        foreach ($orders->toArray() as &$order) {
            $typeSubService = mb_convert_case($order['tipo_servico'], MB_CASE_LOWER, 'UTF-8');
            $typeService = null;
            $dateTime = Carbon::parse($order['data_agendamento']);
            $dayName = $dateTime->dayName;
            $date = $dateTime->toDateString();
            $period = $dateTime->hour < 12 ? 'manha' : 'tarde';

            foreach ($services as $key => $service) {

                foreach($service['subServices'] as $k => $v) {

                    $subServiceTitle = mb_convert_case($v->titulo, MB_CASE_LOWER, 'UTF-8');
                    $serviceTitle = mb_convert_case($service->titulo, MB_CASE_LOWER, 'UTF-8');


                    if ($subServiceTitle == $typeSubService) {
                        $typeService = $serviceTitle;
                        break;

                    }

                }


                foreach ($service['capacityWeekly'] as $v) {
                    if ($v->dia_semana == $dayName && $service->id == $v->servico_id &&
                        $subServiceTitle == mb_convert_case($order['tipo_servico'], MB_CASE_LOWER, 'UTF-8')
                    ) {
                        $hour = $dateTime->hour;

                        // Determina o período do dia
                        if ($hour < 12) {
                            $order['periodo'] = 'manha';
                        } elseif ($hour < 18) {
                            $order['periodo'] = 'tarde';
                        } else {
                            $order['periodo'] = 'noite';
                        }

                        if ($hour >= intval($v->hora_inicio) && $hour < intval($v->hora_fim)) {
                            $order['status_id'] = 1;
                            break;
                        }

                        $order['status_id'] = 12;
                    }
                }

            }



            $capacityVerify = $capacity->where('data', $date)
                ->where('periodo', $order['periodo'])
                ->where('servico', $typeService)
                ->first();


            if ($capacityVerify) {

                if($capacityVerify->status == 'fechada') {
                    $order['status_id'] = 14;
                }

                if($capacityVerify->utilizado >= $capacityVerify->capacidade) {
                    $order['status_id'] = 13;
                }

            }

//            if (!isset($grouped[$date])) {
//                $grouped[$date] = [];
//            }
//            if (!isset($grouped[$date][$period])) {
//                $grouped[$date][$period] = [];
//            }
//
//            if (!isset($grouped[$date][$period][$typeService])) {
//                $grouped[$date][$period][$typeService] = [];
//            }
//
//            if (!isset($grouped[$date][$period][$typeService][$typeSubService])) {
//                $grouped[$date][$period][$typeService][$typeSubService] = [];
//            }



            $grouped[] = $order;

            $orderUpdate->where('protocolo', $order['protocolo'])->update([
                'status_id' => $order['status_id']
            ]);
        }


        return $grouped;

    }

    private function getData()
    {
        $this->data = DB::connection('voalle')->select($this->getQuery());
    }

    private function getQuery() : string
    {
        $query = '
         select distinct coalesce(contract_service_tags.contract_id, \'000000\') as "contract_id",
        assignments.requestor_id as "client_id",
        assignment_incidents.protocol as "protocol",
        (select max(pa.neighborhood) from erp.people_addresses pa where pa.person_id = cliente.id limit 1) AS "neighborhood",
        (select max(pa.postal_code) from erp.people_addresses pa where pa.person_id = cliente.id limit 1) AS "cep",
        cliente.tx_id as "doc",
        (select max(pa.city) from erp.people_addresses pa where pa.person_id = cliente.id limit 1)  AS "city",
        cliente.name as "client_name",
        (select max(pa.address_complement) from erp.people_addresses pa where pa.person_id = cliente.id limit 1) AS "complement",
        TO_CHAR(assignments.beginning_date::DATE,\'YYYY-MM-DD\') as "beginning_date",
        people.email as "email",
        people.lat as "latitude",
        (select max(pa.street) from erp.people_addresses pa where pa.person_id = cliente.id limit 1)  AS "address",
        people.lng as "longitude",
        cliente.number AS "number",
        cliente.cell_phone_1 as "cell_phone",
        cliente.phone as  "cell_phone_2",
        \'INDIFERENTE\' as "type_immobile",
        incident_types.title as "type_service",
        incident_status.title as "status_title",
        incident_status.id as "status_id",
        case WHEN cliente.neighborhood = \'Recanto das Emas\' THEN \'RECANTO DAS EMAS\'
        WHEN cliente.neighborhood = \'Samambaia Sul (Samambaia)\' THEN \'Samambaia\'
        WHEN cliente.neighborhood = \'Ceilândia Norte (Ceilândia)\' THEN \'Ceilândia Norte\'
        WHEN cliente.neighborhood = \'Samambaia Norte (Samambaia)\' THEN \'Samambaia Norte\'
        WHEN cliente.neighborhood = \'Ceilândia Sul (Ceilândia)\' THEN \'Ceilândia Sul\'
        WHEN cliente.neighborhood = \'Santa Maria\' THEN \'Santa Maria\'
        WHEN cliente.neighborhood = \'Riacho Fundo II\' THEN \'Riacho Fundo II\'
        WHEN cliente.neighborhood = \'Taguatinga Norte (Taguatinga)\' THEN \'taguatinga Norte\'
        WHEN cliente.neighborhood = \'Paranoá\' THEN \'Paranoá\'
        WHEN cliente.neighborhood = \'Riacho Fundo I\' THEN \'Riacho Fundo I\'
        WHEN cliente.neighborhood = \'Setor Habitacional Vicente Pires\' THEN \'Vicente Pires\'
        WHEN cliente.neighborhood = \'Setor Oeste (Gama)\' THEN \'Setor Oeste\'
        WHEN cliente.neighborhood = \'Ponte Alta Norte (Gama)\' THEN \'PONTE ALTA (GAMA)\'
        WHEN cliente.neighborhood in (\'Sobradinho\', \'Nova Colina (Sobradinho)\', \'Setor Econômico de Sobradinho (Sobradinho)\', \'Setor Oeste (Sobradinho II)\') THEN \'Sobradinho\'
        WHEN cliente.neighborhood = \'Setor Sul (Gama)\' THEN \'Setor Sul\'
        WHEN cliente.neighborhood = \'Areal (Águas Claras)\' THEN \'Areal\'
        WHEN cliente.neighborhood = \'Taguatinga Sul (Taguatinga)\' THEN \'Taguatinga Sul\'
        WHEN cliente.neighborhood = \'Varjão\' THEN \'Varjão\'
        WHEN cliente.neighborhood = \'Paranoá Parque (Paranoá)\' THEN \'Paranoã Parque\'
        WHEN cliente.neighborhood = \'Setor Central (Gama)\' THEN \'GAMA\'
        WHEN cliente.neighborhood = \'Guará II\' THEN \'Guará II\'
        WHEN cliente.neighborhood = \'Asa Sul\' THEN \'Asa Sul\'
        WHEN cliente.neighborhood = \'Asa Norte\' THEN \'Asa Norte\'
        WHEN cliente.neighborhood = \'Vila Planalto\' THEN \'Vila Planalto\'
        WHEN cliente.neighborhood = \'Cruzeiro Velho\' THEN \'Cruzeiro Velho\'
        WHEN cliente.neighborhood = \'Samambaia\' THEN \'Samambaia\'
        WHEN cliente.neighborhood in (\'Setor Norte (Planaltina)\', \'Arapoanga (Planaltina)\', \'Vale do Amanhecer (Planaltina)\', \'Setor Residencial Leste (Planaltina)\') THEN \'Planaltina\'
        WHEN cliente.neighborhood = \'Setor de Mansões de Sobradinho\' THEN \'Sobradinho\'
        WHEN cliente.neighborhood = \'Setor Habitacional Jardim Botânico\' THEN \'Jardim Botânico\'
        WHEN cliente.neighborhood = \'Setor Industrial (Taguatinga)\' THEN \'Setor Industrial\'
        WHEN cliente.neighborhood = \'Sudoeste/Octogonal\' THEN \'Sudoeste\'
        WHEN cliente.neighborhood = \'Núcleo Bandeirante\' THEN \'Núcleo Bandeirante\'
        WHEN cliente.neighborhood = \'Candangolândia\' THEN \'Candangolândia\'
        WHEN cliente.neighborhood = \'Águas Claras\' THEN \'Aguas Claras\'
        WHEN cliente.neighborhood = \'Riacho Fundo\' THEN \'Riacho Fundo\'
        WHEN cliente.neighborhood = \'Noroeste\' THEN \'Noroeste\'
        WHEN cliente.neighborhood = \'Guará I\' THEN \'Guará\'
        WHEN cliente.neighborhood = \'Estrutural\' THEN \'Estrutural\'
        WHEN cliente.neighborhood = \'Cruzeiro Novo\' THEN \'Cruzeiro\'
        WHEN cliente.neighborhood = \'SIA\' THEN \'SIA\'
        WHEN cliente.neighborhood = \'SOF Sul\' THEN \'Sof Sul\'
        WHEN cliente.neighborhood = \'SOF Norte\' THEN \'Sof Norte\'
        WHEN cliente.neighborhood = \'SCIA\' THEN \'Scia\'
        WHEN cliente.neighborhood = \'Santa Maria\' THEN \'Santa Maria\'
        WHEN cliente.neighborhood = \'Saan\' THEN \'Saan\'
        WHEN cliente.neighborhood = \'Guará\' THEN \'Guará\'
        WHEN cliente.neighborhood = \'Fercal\' THEN \'Fercal\'
        WHEN cliente.neighborhood in (\'Itapoã\', \'Fazendinha (Itapoã)\', \'Del Lago II (Itapoã)\', \'Del Lago I (Itapoã)\') THEN \'Itapoã\'
        WHEN cliente.neighborhood = \'Ponte Alta (Gama)\' THEN \'Ponte\'
        WHEN cliente.neighborhood IN (\'Sobradinho\', \'Sobradinho I\', \'Sobradinho II\', \'Condomínio Mansões Sobradinho (Sobradinho)\', \'Setor Habitacional Contagem (Sobradinho)\') THEN \'Sobradinh\'
        WHEN cliente.neighborhood IN (\'Taguatinga\', \'Taguatinga Norte\', \'Taguatinga Sul\', \'taguatinga Norte\',\'Setor Habitacional Vereda Grande (Taguatinga)\', \'Taguatinga Centro (Taguatinga)\', \'Taguatinga Sul\') THEN \'Taguatinga\'
        WHEN cliente.neighborhood IN (\'São Sebastião\', \'Setor Tradicional (São Sebastião)\') THEN \'São Sebastião\'
        WHEN cliente.neighborhood IN (\'Park Way\') THEN \'Park Way\'
        WHEN cliente.neighborhood IN (\'Candangolândia\') THEN \'Candangolândia\'
        WHEN cliente.neighborhood IN (\'Aguas Claras\', \'Areal\', \'Areal (Águas Claras)\', \'Área de Desenvolvimento Econômico (Águas Claras)\', \'Setor Habitacional Arniqueira (Águas Claras)\') THEN \'Águas Claras\'
        WHEN cliente.neighborhood IN (\'Asa Sul\', \'Asa Norte\') THEN \'Asa Sul/Norte\'
        WHEN cliente.neighborhood = \'Vila Planalto\' THEN \'Vila Planalto\'
        WHEN cliente.neighborhood IN (\'Noroeste\') THEN \'Noroeste\'
        WHEN cliente.neighborhood IN (\'Sol Nascente\', \'Pôr do Sol\') THEN \'Sol Nascente/Pôr do Sol\'
        WHEN cliente.neighborhood IN (\'Arniqueira\', \'Arniqueiras\') THEN \'Arniqueira\'
        WHEN cliente.neighborhood IN (\'Brazlândia\') THEN \'Brazlândia\'
        WHEN cliente.neighborhood IN (\'Polo JK\') THEN \'Polo JK\'
        WHEN cliente.neighborhood IN (\'Vila Nossa Senhora de Fátima (Planaltina)\', \'Planaltina\', \'Quintas do Amanhecer II (Planaltina)\', \'Jardim Roriz (Planaltina)\', \'Estância Planaltina (Planaltina)\',\'Setor Residencial Leste (Planaltina)\', \'Setor Tradicional (Planaltina)\') THEN \'Planaltina\'
        WHEN cliente.neighborhood IN (\'Lago Norte\') THEN \'Lago Norte\'
        WHEN cliente.neighborhood IN (\'Taquari\') THEN \'Taquari\'
        WHEN cliente.neighborhood IN (\'Núcleo Bandeirante\', \'Setor Placa da Mercedes (Núcleo Bandeirante)\', \'Setor de Indústrias Bernardo Sayão (Núcleo Bandeirante)\', \'Metropolitana (Núcleo Bandeirante)\') THEN \'Núcleo Bandeirante\'
        WHEN cliente.neighborhood IN (\'Vila Cauhy\') THEN \'Vila Cauhy\'
        WHEN cliente.neighborhood IN (\'Setor Habitacional Jardim Botânico\') THEN \'Jardim Botânico\'
        WHEN cliente.neighborhood IN (\'Setor Residencial Oeste (São Sebastião)\', \'Vila Nova (São Sebastião)\', \'Vila São José (São Sebastião)\') THEN \'São Sebastião\'
        WHEN cliente.neighborhood IN (\'Setor Habitacional Ribeirão (Santa Maria)\') THEN \'Santa Maria\'
        WHEN cliente.neighborhood = \'PADRE MIGUEL\' THEN \'PADRE MIGUEL\'
        WHEN cliente.neighborhood in (\'Setor Tradicional (Brazlândia)\', \'Setor Sul (Brazlândia)\', \'Setor Norte (Brazlândia)\', \'Vila São José (Brazlândia)\') then \'Brazlândia\'
        WHEN cliente.neighborhood = (\'Setor Habitacional Vicente Pires - Trecho 3\') then \'Vicente Pires\'
        ELSE cliente.neighborhood
    	END as "Node",
    	case WHEN cliente.neighborhood in (\'Recantos das Emas \', \'RECANTO DS EMAS\', \'RECANTO DAS EMAS \', \'RECANTO DAS EMAS\', \'Recanto Das Emas\', \'Recanto das Emas \', \'Recanto das Emas\', \'RECANTO DA EMAS\', \'Área Rural do Recanto das Emas\', \'Recanto das Emas\') THEN \'Recanto das Emas\'
        WHEN cliente.neighborhood = \'Samambaia Sul (Samambaia)\' THEN \'Samambaia\'
        WHEN cliente.neighborhood = \'Ceilândia Norte (Ceilândia)\' THEN \'Ceilândia\'
        WHEN cliente.neighborhood = \'Samambaia Norte (Samambaia)\' THEN \'Samambaia\'
        WHEN cliente.neighborhood in (\'Ceilandia Norte Ceilandia\', \'Ceilândia Sul (Ceilândia)\', \'Setor Habitacional Pôr do Sol (Ceilândia)\') THEN \'Ceilândia\'
        WHEN cliente.neighborhood = \'Santa Maria\' THEN \'Santa Maria\'
        WHEN cliente.neighborhood = \'Riacho Fundo II\' THEN \'Riacho Fundo\'
        WHEN cliente.neighborhood = \'Taguatinga Norte (Taguatinga)\' THEN \'Taguatinga\'
        WHEN cliente.neighborhood in (\'PARANOA PARQUE\', \'paranoa parque \', \'Paranoá\', \'paranoá\',\'PARANOA\', \'Paranoa\', \'Paranoá\', \'Paranoá Parque (Paranoá)\') THEN \'Paranoá\'
        WHEN cliente.neighborhood = \'Riacho Fundo I\' THEN \'Riacho Fundo\'
        WHEN cliente.neighborhood = \'Setor Habitacional Vicente Pires\' THEN \'Vicente Pires\'
        WHEN cliente.neighborhood in (\'Vila Rabelo I (Sobradinho)\', \'Vila Rabelo II (Sobradinho)\', \'Vale das Acácias (Sobradinho)\', \'Sol nascente\', \'Sobradinho  II\', \' (Sobradinho II)\', \'sobradinho df\', \'Sobradinho 2\', \'Sobradinho 1\', \'Sobradinho 01 vila  DNOCS\', \'SOBRADINHO\', \'Sobradinho \', \'Sobradinho\', \'sobradinho\', \'Setor Economico de Sobradinho (Sobradinho)\', \'Grande Colorado (Sobradinho)\',\'Condomínio Mirante da Serra (Sobradinho)\', \'Condomínio Império dos Nobres (Sobradinho)\', \'Condomínio Comercial e Residencial Sobradinho (Sobradinho)\', \'Buriti 2 \', \'Buritizinho (Sobradinho II) \', \'Sobradinho\', \'Nova Colina (Sobradinho)\', \'Setor Econômico de Sobradinho (Sobradinho)\', \'Setor Oeste (Sobradinho II)\', \'Setor de Habitações Individuais Sul\') THEN \'Sobradinho\'
        WHEN cliente.neighborhood = \'Areal (Águas Claras)\' THEN \'Águas Claras\'
        WHEN cliente.neighborhood = \'Taguatinga Sul (Taguatinga)\' THEN \'Taguatinga\'
        WHEN cliente.neighborhood = \'Varjão\' THEN \'Varjão\'
        WHEN cliente.neighborhood in (\'Zona Industrial (Guará)\', \'Zona Industrial Guara\', \'Zona Industrial\', \'Vila Estrutural\', \'Setor Oeste (Vila Estrutural)\', \'Guará II POLO DE MODAS\', \'GUARA II\', \'Guara II\', \'Guara I\', \'Guará 2\', \'GUARÁ\', \'Guará\', \'Guará II\') THEN \'Guará\'
        WHEN cliente.neighborhood = \'Asa Sul\' THEN \'Asa Sul\'
        WHEN cliente.neighborhood = \'Asa Norte\' THEN \'Asa Norte\'
        WHEN cliente.neighborhood = \'Vila Planalto\' THEN \'Vila Planalto\'
        WHEN cliente.neighborhood = \'Cruzeiro Velho\' THEN \'Cruzeiro\'
        WHEN cliente.neighborhood = \'Samambaia\' THEN \'Samambaia\'
        WHEN cliente.neighborhood in (\'Vila Feliz (Planaltina)\', \'Vale do Sol (Planaltina)\', \'Vale do Amanhecer Planaltina\', \'Setor Habitacional Mestre DArmas Planaltina\', \'Setor Comercial Central (Planaltina)\', \' ( horta comunitária) Setor Residencial Leste (Planaltina)\', \'Fazendinha \', \'Fazendinha\', \'Fazenda Mestre DArmas (Etapa II - Planaltina)\',\'Estancia 5 (Planaltina)\', \'Estância 3\', \'Estância 1 Planaltina (Planaltina)\', \'Condomínio São Francisco I (Planaltina) Araponga\', \'Condomínio Santa Mônica (Planaltina)\', \'Condomínio Residencial Morada Nobre (Planaltina)\', \'Condomínio Prado (Planaltina)\', \'Condomínio Porto Rico\',\'Condomínio Parque Mônaco (Planaltina)\', \'Condomínio Parque Mônaco II (Planaltina)\', \'Condomínio Nova Esperança (Planaltina)\', \'Condomínio Nosso Lar (Planaltina)\', \'Condominio Mestre DArmas IV (Planaltina)\',\'Condomínio Guirra (Planaltina)\', \'Condomínio Coohaplan - Itiquira (Planaltina)\', \'Vila Nossa Senhora de Fátima (Planaltina)\',\'San Sebastian (Planaltina)\', \'Setor Norte (Planaltina)\', \'Arapoanga (Planaltina)\', \'Vale do Amanhecer (Planaltina)\', \'Setor Residencial Leste (Planaltina)\', \'Recanto Feliz (Planaltina)\') THEN \'Planaltina\'
        WHEN cliente.neighborhood = \'Setor de Mansões de Sobradinho\' THEN \'Sobradinho\'
        WHEN cliente.neighborhood = \'Setor Habitacional Jardim Botânico\' THEN \'Jardim Botânico\'
        WHEN cliente.neighborhood = \'Setor Industrial (Taguatinga)\' THEN \'Taguatinga\'
        WHEN cliente.neighborhood = \'Sudoeste/Octogonal\' THEN \'Sudoeste\'
        WHEN cliente.neighborhood = \'Núcleo Bandeirante\' THEN \'Núcleo Bandeirante\'
        WHEN cliente.neighborhood = \'Candangolândia\' THEN \'Candangolândia\'
        WHEN cliente.neighborhood = \'Águas Claras\' THEN \'Águas Claras\'
        WHEN cliente.neighborhood = \'Riacho Fundo\' THEN \'Riacho Fundo\'
        WHEN cliente.neighborhood = \'Noroeste\' THEN \'Noroeste\'
        WHEN cliente.neighborhood = \'Guará I\' THEN \'Guará\'
        WHEN cliente.neighborhood = \'Estrutural\' THEN \'Estrutural\'
        WHEN cliente.neighborhood = \'Cruzeiro Novo\' THEN \'Cruzeiro\'
        WHEN cliente.neighborhood = \'SIA\' THEN \'Guará\'
        WHEN cliente.neighborhood = \'SOF Sul\' THEN \'Guará\'
        WHEN cliente.neighborhood = \'SOF Norte\' THEN \'Guará\'
        WHEN cliente.neighborhood = \'SCIA\' THEN \'Guará\'
        WHEN cliente.neighborhood = \'Saan\' THEN \'Guará\'
        WHEN cliente.neighborhood = \'Guará\' THEN \'Guará\'
        WHEN cliente.neighborhood in (\'NOVA COLINA (Sobradinho)\', \'Condomínio Vale dos Pinheiros (Sobradinho)\', \'Fercal\') THEN \'Sobradinho\'
        WHEN cliente.neighborhood in (\'Região dos Lagos  Itapoã\', \'Itapoã Parque (Itapoã)\', \'ITAPOAN II\', \'Itapoã I\', \' (Itapoã I)\', \'Itapoa I\', \'Itapoa,del lago\', \'ITAPOÃ 2\', \'Itapoã 1 \', \'ITAPOÃ\', \'Itapoã \', \'Itapoã\', \'ITAPOA\', \'Fazendinha (Itapoã II)\', \'Fazendinha Itapoã I\', \'Fazendinha Itapoa\',\'Fazendinha 2 (Itapoã)\', \'Del Lago (Itapoã)\', \'Del Lago I Itapoa\', \'Del Lago II Itapoa\',\'Itapoã\', \'Fazendinha (Itapoã)\', \'Del Lago II (Itapoã)\', \'Del Lago I (Itapoã)\', \'Itapoã II\') THEN \'Itapoã\'
        WHEN cliente.neighborhood = \'Lago Sul\' THEN \'Lago Sul\'
        WHEN cliente.neighborhood = \'Lago Norte\' THEN \'Lago Norte\'
        WHEN cliente.neighborhood = \'Arniqueira\' THEN \'Arniqueiras\'
        WHEN cliente.neighborhood = \'Sol Nascente/Pôr do Sol\' THEN \'Ceilândia\'
        WHEN cliente.neighborhood = \'Brazlândia\' THEN \'Brazlândia\'
        WHEN cliente.neighborhood in (\'Parque da Barragem Setor 12\', \'Park Way \', \'Park Way\', \'Park Way\', \'Núcleo Rural Vargem Bonita (Park Way)\') THEN \'Park Way\'
        WHEN cliente.neighborhood IN (\'Setor Habitacional Fercal (Sobradinho)\', \'Setor Habitacional Colonia agricula Samambaia - Trecho 3\', \'Samambaia SUL(Samambaia)\', \'Samambaia SUL (Samambaia)\', \'Samambaia Sul Samambaia\', \'Samambaia Sul (Samambai\', \'SAMAMBAIA SUL\', \'Samambaia Sul \', \'Samambaia sul \', \'Samambaia sul\', \'Samambaia Norte(Samambaia)\', \'Samambaia Norte Samambaia\', \'SAMAMBAIA NORTE\', \'Samambaia Norte \', \'SAMAMBAIA \', \'SAMAMBAIA\', \'Samambaia \', \'Samambaia\', \'SAMABAIA\', \'COL SAMAMBAIA / Setor Habitacional Vicente Pires - Trecho 3\', \'COLONIA AGRICULA SAMAMBAIA\', \'Colônia Agrícola Samambaia\', \'colônia agrícola samambaia \', \'colonia agricola samambaia\', \'Samambaia Norte\', \'Samambaia Sul\', \'Samambaia\') THEN \'Samambaia\'
        WHEN cliente.neighborhood IN (\'Setor Leste\', \'Setor Norte\', \'Setor Oeste\', \'Setor Sul\', \'Quadras Econômicas Lúcio Costa (Guará)\', \'Setor Industrial\', \'Sia\', \'Cidade do Automóvel\', \'Saan\', \'Sof Norte\', \'Sof Sul\', \'sia\', \'Scia\', \'MICRO ÁREA\', \'SIA\') THEN \'Guará\'
        WHEN cliente.neighborhood = \'Varejão\' THEN \'Varejão\'
        WHEN cliente.neighborhood IN (\'Paranoã Parque\', \'Paranoá\') THEN \'Paranoá\'
        WHEN cliente.neighborhood IN (\'SOL NASCENTE\', \'SH Sol Nascente QCS 2 Conj. I - Ceilândia, Brasília - DF\', \'Setor Residencial Leste Buritis 4 \', \'  SETOR P SUL \', \'INGRA - CEILANDIA \', \'Expansão do Setor O Ceilândia Norte (Ceilândia)\', \'Expansão do setor O\', \'Condomínio Vencedor- sol nascente\', \'Condominio Prive Lucena Roriz Ceilandia\', \'Ceilndia Norte Ceilndia\', \'Ceilândia Sul (Ceilândia) Vila Madureira\', \'Ceilândia Sul (Ceilândia)  Sol nascente\', \'Ceilandia Sul Ceilandia\', \'CEILANDIA SUL\', \'Ceilândia P Sul \', \'Ceilândia Norte (Ceilândia) Setor O\', \'CEILÂNDIA NORTE (CEILÂNDIA)\', \'Ceilândia NORTE (Ceilândia)\', \'Ceilândia norte (Ceilândia)\', \'Ceilandia Norte Ceilândia\', \'Ceilândia Norte \', \'CEILANDIA NORTE\', \'Ceilandia Norte \', \'Ceilandia Norte\', \'Ceilandia \', \'Área de Desenvolvimento Econômico (Ceilândia)\', \'Ceilândia Norte (Ceilândia)\', \'Ceilândia Norte\', \'Ceilândia Sul\', \'Ceilândia Centro (Ceilândia)\',\'Setor Habitacional Sol Nascente (Ceilândia)\', \'Condomínio Privê Lucena Roriz (Ceilândia)\') THEN \'Ceilândia\'
        WHEN cliente.neighborhood IN (\'Riacho Fundo 2\', \'riacho fundo 2\', \'SETOR RESIDENCIAL NORTE\', \'RIACHO II\', \'riacho fundo l\', \'RIACHO FUNDO II\', \'Riacho Fundo I ( COLONIA AGRÍCOLA SUCUPIRA)\', \'RIACHO FUNDO I\', \'Riacho Fundo 2 \', \'Riacho Fundo 2\'\', riacho fundo 2\', \'Riacho Fundo\', \'Riacho Fundo I\', \'Riacho Fundo II\', \'Riacho Fundo\') THEN \'Riacho Fundo\' WHEN cliente.neighborhood IN (\'Vicente Pires\', \'Setor Habitacional Vicente Pires\') THEN \'Vicente Pires\' WHEN cliente.neighborhood IN (\'Recanto das Emas\', \'RECANTO DAS EMAS\') THEN \'Recanto das Emas\' WHEN cliente.neighborhood IN (\'Setor sul (Gama) area verde\', \'SETOR SUL GAMA\', \'Setor Sul Gama\', \'Setor Oeste(Gama)\',\'Setor Oeste - Gama\', \'Setor Oeste Gama\', \'Setor oeste (Gama)\', \'Setor oeste (Gama)\', \'setor oeste (Gama)\', \'Setor Norte (Gama)\', \'Setor Noroeste\', \'Setorl Sul(Gama)\', \'Setor Leste Gama \', \'Setor Leste Gama\', \'SETOR LESTE \', \'Setor Industrial (Gama Leste)\', \'Setor Industrial Gama\', \'Setor Central (Gama) \', \'Setor Central Gama\', \'PONTE ALTA\', \'Setor Central (Gama)\', \'Setor Oeste (Gama)\', \'Ponte Alta Norte (Gama)\', \'Setor Sul (Gama)\', \'Setor leste (Gama)\', \'N. RURAL PONTE ALTA NORTE\', \'GAMA SETOR SUL\', \'GAMA ( PONTE ALTA)\', \'GAMA OESTE\', \'Gama Oeste\', \'GAMA LESTE\', \'GAMA\', \'Gama\', \'Ponte Alta (Gama)\', \'GAMA\', \' Ponte Alta Norte Gama DF\', \'PONTE ALTA NORTE GAMA \', \'PONTE ALTA NORTE GAMA\', \'Ponte Alta Norte (Gama) \', \'Ponte Alta Norte Gama\', \'ponte alta norte do Gama\', \'PONTE ALTA NORTE\', \'PONTE ALTA \', \'Ponte Alta \', \' NÚCLERO RURAL GAMA \', \'Engenho das Lages (Gama)\', \'GAMA\', \'PONTE ALTA (GAMA)\', \'Setor Oeste (Gama)\', \'Setor Sul (Gama)\', \'Setor Central (Gama)\',\'Setor Industrial (Gama)\', \'Setor Leste (Gama)\') THEN \'Gama\' WHEN cliente.neighborhood IN (\'SIG\', \'Setor Norte (Vila Estrutural)\', \'Setor Leste (Vila Estrutural)\', \'Guará\', \'Guará II\', \'Guará I\') THEN \'Guará\' WHEN cliente.neighborhood IN (\'Estrutural\', \'Cidade do Automóvel\') THEN \'Estrutural\' WHEN cliente.neighborhood IN (\'Sudoeste\', \'Octogonal\') THEN \'Sudoeste\' WHEN cliente.neighborhood IN (\'Setor de Mansões Dom Bosco (Lago Sul)\',\'Setor de Habitações Individuais Sul / LAGO SUL \', \'Setor de Habitações Individuais Sul - Lago Sul\', \'Lago Sul\', \'Lake Side\') THEN \'Lago Sul\' WHEN cliente.neighborhood IN (\'Cruzeiro Velho\', \'Cruzeiro\', \'Cruzeiro Novo\') THEN \'Cruzeiro\' WHEN cliente.neighborhood IN (\'Vila Varjão do Torto\', \'varjão do torto\', \'VARJÃO\', \'Varjão\', \'Varjao\', \'Setor de Habitacoes Individuais Sul\', \'Setor de Habitações Individuais Norte/Vila Varjão do Torto\', \'Setor de Habitações Individuais Norte - Varjão \', \'Setor de Habitações Individuais Norte\', \'Varjão\', \'Setor de Habitacoes Individuais Norte\') THEN \'Varjão\' WHEN cliente.neighborhood IN (\'Setor Oeste Sobradinho II\', \'Setor Oeste (Sobradinho I)\', \'Setor Oeste sobradinho\', \'Setor Industrial (Sobradinho)\', \'Setor de Mansões de Sobradinho 2\', \'Serra Azul (Sobradinho)\', \'Região dos Lagos (Sobradinho)\', \'Núcleo Rural Lago Oeste (Sobradinho)\',\'Sobradinho\',\'Alto da Boa Vista (Sobradinho)\', \'Sobradinho I\', \'Sobradinho II\', \'Condomínio Mansões Sobradinho (Sobradinho)\', \'Setor Habitacional Contagem (Sobradinho)\') THEN \'Sobradinho\' WHEN cliente.neighborhood IN (\'Taguatinha Sul\', \'Taguatinga SUL (Taguatinga)\', \'Taguatinga Sul Taguatinga\', \'Taguatinga Sul Taguatinga\', \'TAGUATINGA NORTE (TAGUATINGA)\', \'Taguatinga NORTE (Taguatinga)\', \'Taguatinga Norte Taguatinga\', \'Taguatinga Norte (aguatinga\', \'TAGUATINGA NORTE\', \'Taguatinga - DF\', \'TAGUATINGA\', \'Taguatinga \', \'Taguatinga\', \'Setor de Desenvolvimento Econômico (Taguatinga)\', \'Taguatinga\', \'Taguatinga Norte\', \'Taguatinga Sul\', \'taguatinga Norte\', \'Setor Habitacional Vereda Grande (Taguatinga)\', \'Taguatinga Centro (Taguatinga)\', \'Taguatinga Sul\') THEN \'Taguatinga\' WHEN cliente.neighborhood IN (\'São Sebastião\', \'Setor Tradicional (São Sebastião)\') THEN \'São Sebastião\' WHEN cliente.neighborhood IN (\'Park Way\') THEN \'Park Way\' WHEN cliente.neighborhood IN (\'Candangolândia\') THEN \'Candangolândia\' WHEN cliente.neighborhood IN (\'Sul (Águas Claras)\', \'Norte (Águas Claras)\', \'Areal (Águas Claras - Taguatinga Sul)\', \'AGUAS CLARAS\', \'Aguas Claras\', \'Areal\', \'Areal (Águas Claras)\', \'Área de Desenvolvimento Econômico (Águas Claras)\', \'Setor Habitacional Arniqueira (Águas Claras)\') THEN \'Águas Claras\' WHEN cliente.neighborhood = \'Vila Planalto\' THEN \'Vila Planalto\' WHEN cliente.neighborhood IN (\'Noroeste\') THEN \'Noroeste\' WHEN cliente.neighborhood IN (\'Setor Industrial (Ceilândia)\', \'Sol Nascente\', \'Pôr do Sol\') THEN \'Ceilândia\' WHEN cliente.neighborhood IN (\'Arniqueiras - Colônia Agrícola\', \'ARNIQUEIRAS\', \'Arniqueiras \', \'Área de Desenvolvimento Econômico (ARNIQUEIRA )\', \'Área de Desenvolvimento Econômico (Águas Claras) \', \'Arniqueira\', \'Arniqueiras\', \'Area de Desenvolvimento Econômico aguas Claras\') THEN \'Águas Claras\' WHEN cliente.neighborhood IN (\'Brazlândia\') THEN \'Brazlândia\' WHEN cliente.neighborhood IN (\'Villa Rabello \', \'Vila Vicentina (Planaltina)\', \'Vila Vicentina\', \'Vila Dimas (Planaltina)\', \'vila buritis - Planaltina df \',\'Veneza I (Planaltina)\', \'Veneza II (Planaltina)\', \'Veneza III (Planaltina)\', \'Veneza I Arapoanga (Planaltina)\', \'Taquara (Planaltina)\', \'Setor tradicional (Planaltina)\', \'Setor Sul (Planaltina)\', \'Setor Residencial Oeste (Planaltina)\', \'Setor Residencial Norte (Planaltina)\', \'Setor Mansões Itiquira (Planaltina)\', \'Setor Hospitalar (Planaltina)\', \'Setor de Mansões Mestre D armas (Planaltina)\',\'Setor de Hotéis e Diversões (Planaltina)\', \'Setor de Educação (Planaltina)\', \'Residencial Sarandy (Planaltina)\', \'Residencial São Francisco I (Planaltina)\', \'Residencial São Francisco II (Planaltina)\', \'Residencial Sandray (Planaltina)\', \'Residencial Paiva I\', \'Residencial Nova Planaltina (Planaltina)\', \'Residencial Nova Esperança (Planaltina)\', \'Residencial Jardim Coimbra\', \'Residencial Flamboyant (Planaltina)\', \'Residencial Condomínio Marissol (Planaltina)\', \'Residencial Bica do DER (Gleba B - Planaltina)\', \'Quintas do Amanhecer III (Planaltina)\', \'Portal do Amanhecer V (Privê - Planaltina)\', \'Portal do Amanhecer V (Planaltina)\', \'Portal do Amanhecer (Planaltina)\', \'Portal do Amanhecer I (Planaltina)\', \'PLANALTINA DF\', \' PLANALTINA - DF\', \'Planaltina DF \', \'Planaltina - DF\', \'Planaltina Arapoanga\', \'PLANALTINA\', \'Planaltina \', \' (Planaltina)\', \'Planaltina\', \'Planalatina\', \'Nossa Senhora de Fátima (Planaltina)\', \'Mansões do Amanhecer (Planaltina)\', \'Arapoanga \', \'Arapongas - Planaltina \', \'ARAPOANGAS\', \'ARAPOANGA\', \'ARAPOANGA (Planaltina)\', \'Planaltina\', \'Quintas do Amanhecer II (Planaltina)\', \'Jardim Roriz (Planaltina)\', \'Estância Planaltina (Planaltina)\', \'Setor Tradicional (Planaltina)\') THEN \'Planaltina\' WHEN cliente.neighborhood IN (\'Setor de Mansões do Lago Norte\', \'Lago Norte\', \'Setor Habitacional Taquari (Lago Norte)\') THEN \'Lago Norte\' WHEN cliente.neighborhood IN (\'Taquari\') THEN \'Ceilândia\' WHEN cliente.neighborhood IN (\'Vila Cauhy (Núcleo Bandeirante)\', \'Vila cauhy núcleo bandeirante\',\'Setor de Postos e Motéis Sul (Núcleo Bandeirante)\', \'Nucleo bandeirante\', \'Núcleo Bandeirante\', \'Nucleo Bandeirante\', \'Vila Cauhy\', \'Nucleo bandeirante , \'\'Núcleo Bandeirante\',\'Área de Desenvolvimento Econômico (Núcleo Bandeirante)\', \'Setor Placa da Mercedes (Núcleo Bandeirante)\', \'Setor de Indústrias Bernardo Sayão (Núcleo Bandeirante)\', \'Metropolitana (Núcleo Bandeirante)\') THEN \'Núcleo Bandeirante\' WHEN cliente.neighborhood IN (\'Setor Habitacional Tororó (Jardim Botânico)\', \'Setor Habitacional Jardim Botânico 3\', \'Setor Habitacional Jardim Botanico\', \'Jardins Mangueiral (Jardim Botânico)\', \'Setor Habitacional Jardim Botânico\') THEN \'Jardim Botânico\' WHEN cliente.neighborhood IN (\'Zumbi dos Palmares - São Sebastião \', \'Vila do Boa (São Sebastião)\', \'Setor Sudoeste\', \'SETOR RESIDENCIAL OESTE (SÃO SEBASTIÃO)\', \'Sebastião/DF\', \'SÃO SEBASTIÃO\', \'São Sebastião\', \'(São Sebastião)\', \'São Gabriel (São Sebastião)\', \'São Francisco (São Sebastião)\', \'São Bartolomeu (São Sebastião)\', \'Residencial Vitória (São Sebastião)\', \'Residencial Morro da Cruz (São Sebastião)\', \'Residencial do Bosque (São Sebastião)\', \'MORRO AZUL (SÃO SEBASTIÃO)\', \'Morro Azul (São Sebastião)\', \'Morro azul(São Sebastião)\', \'João Cândido (São Sebastião)\', \'Crixá (São Sebastião)\', \'Bonsucesso (São Sebastião)\', \'Bela Vista (São Sebastião)\', \'BAIRRO CENTRO (São Sebastião)\', \'Setor Residencial Oeste (São Sebastião)\', \'Vila Nova (São Sebastião)\', \'Vila São José (São Sebastião)\', \'Centro (São Sebastião)\') THEN \'São Sebastião\' WHEN cliente.neighborhood IN (\'Setor Meireles Santa Maria\', \'Setor Meireles (Santa Maria)\', \'Polo JK\',\'Setor Habitacional Ribeirao Santa Maria\', \'Santa Maria Sul \', \'Santa Maria - Sul\', \'Santa Maria Sul\', \'Santa Maria sul \', \'Santa Maria sul\', \'Santa Maria Norte \', \'Santa Maria Norte\', \'Santa Maria norte\', \'Santa Maria - Condomínio Porto Rico\', \'SANTA MARIA \', \'SANTA MARIA\', \'Santa Maria\', \'Residencial Santos Dumont (Santa Maria)\', \'Núcleo Rural Santa Maria\', \'Núcleo Rural Alagados (Santa Maria)\', \'Condomínio Residencial Santa Maria (Santa Maria)\', \'CONDOMÍNIO PORTO RICO SANTA MARIA\', \'Cidade Nova (Santa Maria)\', \'Área Rural de São Sebastião\', \'Setor Habitacional Ribeirão (Santa Maria)\') THEN \'Santa Maria\' WHEN cliente.neighborhood = \'PADRE MIGUEL\' THEN \'PADRE MIGUEL\' WHEN cliente.neighborhood in (\'Setor Tradicional (Brazlândia)\', \'Setor Sul (Brazlândia)\', \'Setor Norte (Brazlândia)\',\'Veredas (Brazlândia)\', \'Vila São José (Brazlândia)\') then \'Brazlândia\' WHEN cliente.neighborhood in (\'Vila São José (Vicente Pires)\', \'Vila São Jose Vicente Pires\', \'VILA SAO JOSE (VICENTE PIRES)\', \'VILA SÃO JOSÉ \', \'Vila São José\', \'Vicente Pires DF\', \'VICENTE PIRES \', \'VICENTE PIRES\', \'Vicente Pires\', \'Vicente pires\', \'Setor Habitacional Vicente Pires - Trecho 3 \', \'Setor Habitacional Vicente Pires Trecho 1\', \'Setor Habitacional Vicente Pires - Trecho 1\', \'Setor Habitacional Vicente Pires- CONDOMINIO ATHENAS\', \'Setor Habitacional Vicente Pires / COL SAMAMBAIA\', \'SETOR HABITACIONAL VICENTE PIRES \', \'Setor Habitacional VICENTE PIRES\', \'Setor Habitacional Samambaia (Vicente Pires)\', \'Setor Habitacional Vicente Pires - Trecho 3\') then \'Vicente Pires\'
        ELSE cliente.neighborhood
    	END as "dispatch_area",
        regexp_replace(assignments.description, \'<[^>]+>\', \'\', \'g\') as "observation",
    	\'DISTRITO FEDERAL\' as "group",
    	assignments.id as "assignment_id",
    	s.start_date as "schedule_date",
    	vu."name" as "created_by",
    	p."name" as "team"
        from erp.assignments
        inner join erp.assignment_incidents on (assignment_incidents.assignment_id = assignments.id )
        inner join erp.incident_types on (incident_types.id = assignment_incidents.incident_type_id)
        inner join erp.incident_status on (assignment_incidents.incident_status_id = incident_status.id)
        left join erp.people cliente ON (cliente.id = assignment_incidents.client_id)
        left join erp.solicitation_classifications on (solicitation_classifications.id = assignment_incidents.solicitation_classification_id)
        left join erp.solicitation_problems on (assignment_incidents.solicitation_problem_id = solicitation_problems.id)
        left join erp.contract_service_tags on (assignment_incidents.contract_service_tag_id = contract_service_tags.id)
        left join erp.authentication_contracts on (authentication_contracts.service_tag_id = contract_service_tags.id)
        inner join erp.people on (assignments.requestor_id = people.id)
        left join erp.contracts on (contracts.client_id = people.id)
        inner join erp.schedules s on s.assignment_id = assignments.id
        left join erp.v_users vu on vu.id = assignments.created_by
        left join erp.profiles p on p.id = vu.profile_id
        where incident_types.active = \'1\' and assignments.deleted = \'0\' and incident_types.deleted = \'0\'
        and incident_status.id <> \'8\'
        and
        (
        select DATE(s.start_date) from erp.schedules s where s.assignment_id = assignments.id order by s.id desc limit 1
        ) between \''.Carbon::now()->subDay(1)->format('Y-m-d')."' and '".Carbon::now()->addDays(10)->format('Y-m-d').'\'
        and incident_types.id in ('.$this->idServices.')
        order by 2 desc';


        return $query;

    }


}
