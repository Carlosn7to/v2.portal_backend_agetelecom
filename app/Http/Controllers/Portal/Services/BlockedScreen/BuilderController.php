<?php

namespace App\Http\Controllers\Portal\Services\BlockedScreen;

use App\Http\Controllers\Controller;
use App\Models\AgeFacilitate\Token;
use App\Models\Portal\AgeCommunicate\BillingRule\Integrator;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Nette\Utils\Random;

class BuilderController extends Controller
{

    private $client;



    public function builder(Request $request)
    {
        $ip = $_SERVER['REMOTE_ADDR'];

        if($ip != '206.204.248.71' && $ip != '206.204.248.51' && $ip != '127.0.0.1') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $command = $request->command;

        switch ($command) {
            case 'identify':
                return $this->identifyClient($request->data);
                break;
            case 'sendToken':
                return $this->sendSmsConfirmation($request->data);
                break;
            case 'confirmToken':
                return $this->confirmToken($request->data);
                break;
            default:
                return response()->json(['message' => 'Comando inválido'], 404);
        }


    }

    private function identifyClient($identify)
    {
        $this->client = (new IdentifyClient($identify))->response();
        if(!$this->client) {
            return response()->json(['message' => 'Nenhum cliente encontrado na base com os dados fornecidos'], 404);
        }
        return response()->json(['message' => 'Cliente encontrado na base.', 'data' => [
            'id' => $this->client['id']
        ]], 200);
    }

    private function confirmToken($token)
    {
        $token = Token::where('token', $token)->whereStatus('pendente')->first();

        if(!$token) {
            return response()->json(['message' => 'Token inválido e/ou expirado'], 404);
        }

        $token->update([
            'status' => 'confirmado',
            'data_alteracao_status' => Carbon::now(),
        ]);

        return response()->json(['message' => 'Token confirmado com sucesso'], 200);

    }

    private function sendSmsConfirmation($idClient)
    {

        $integrator = json_decode(Integrator::whereId(1)->first('configuracao'), true)['configuracao']['configuration'];

        $clientData = $this->getDataClient($idClient);

        if(!$clientData) {
            return response()->json(['message' => 'Cliente não encontrado na base'], 404);
        }

        // Configurar o cliente Guzzle
        $client = new Client([
            'base_uri' => $integrator['host'],
            'http_errors' => false, // Impedir que Guzzle gere exceções para códigos de erro HTTP
        ]);

        $token = Random::generate(6, '0-9');

        $response = $client->post('sms/2/text/advanced', [
            'headers' => [
                'Authorization' => $integrator['apiKey'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
                'messages' => [
                    [
                        'destinations' => ['to'=> '55'.'61984700440'],//$this->client['cellphone']],
                        'from' => 'Age Telecom',
                        'text' => 'Seu código Age Fibra é: '.$token,
                        'entityId' => 'portal_agetelecom_colaborador',
                        'applicationId' => 'portal_agetelecom_colaborador'
                    ],
                ],
            ],
        ]);

        if($response->getStatusCode() != 200) {
            return response()->json(['message' => 'Erro ao enviar SMS'], 500);
        }

        $registerToken = new Token();

        $result = $registerToken->create([
            'token' => $token,
            'celular' => $clientData['cellphone'],
        ]);

        return response()->json(['message' => 'SMS enviado com sucesso.'], 200);

    }

    private function getDataClient($idClient)
    {
        $query = "select p.id, p.tx_id, p.cell_phone_1 as cellphone from erp.people p where p.id = :id";
        $result = \DB::connection('voalle')->select($query, ['id' => $idClient]);


        if(count($result) > 0) {
            return [
                'id' => $result[0]->id,
                'tx_id' => $result[0]->tx_id,
                'cellphone' => $this->removeCharacters($result[0]->cellphone),
            ];
        }

        return false;
    }

    private function removeCharacters($identify)
    {
        return preg_replace('/[^0-9]/', '', $identify);
    }


}
