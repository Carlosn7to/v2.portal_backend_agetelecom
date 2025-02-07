<?php

namespace App\Http\Controllers\Integrator\Voalle\Billets;

use App\Http\Controllers\Controller;
use App\Models\Integrator\Voalle\BilletLog;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Nette\FileNotFoundException;

class BilletController extends Controller
{

    private $access;


    public function __construct()
    {
        $this->middleware('portal.integrator.voalle.billets.access');
        $this->authenticateVoalle();

    }

    public function getBillet($id)
    {

        $result = $this->getBilletVoalleAndStorage($id);


        if($result){
            return response()->json([
                'success' => true,
                'message' => 'Boleto gerado com sucesso'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erro ao gerar boleto'
        ]);

    }


    private function authenticateVoalle()
    {
        $client = new Client();

        $dataForm = [
            "grant_type" => "client_credentials",
            "scope" => "syngw",
            "client_id" => config('services.voalle.client_id'),
            "client_secret" => config('services.voalle.client_secret'),
            "syndata" => config('services.voalle.syndata')
        ];

        $response = $client->post('https://erp.agetelecom.com.br:45700/connect/token', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => $dataForm
        ]);

        $this->access = json_decode($response->getBody()->getContents());
    }

    private function getBilletVoalleAndStorage($id) : bool
    {
        $client = new Client();

        $responseBillet = $client->get('https://erp.agetelecom.com.br:45715/external/integrations/thirdparty/GetBillet/'.$id,[
            'headers' => [
                'Authorization' => 'Bearer '.$this->access->access_token
            ]
        ]);

        $billetPath = [];

        $billetLog = new BilletLog();

        // Verifique se a requisição foi bem-sucedida (código de status 200)
        if ($responseBillet->getStatusCode() == 200) {

            $pdfContent = $responseBillet->getBody()->getContents();

            $options = [
                'ACL' => 'public-read'
            ];

            $aws = Storage::disk('aws_digitro')->put('boletos/' . 'boleto_' . $id . '.pdf', $pdfContent, $options);

            if($aws){

                $billetLog->create([
                   'fatura_id' => $id,
                   'status' => 1
                ]);

                return true;
            }

            $billetLog->create([
                'fatura_id' => $id,
                'status' => 2
            ]);

            return false;
        }

        $billetLog->create([
            'fatura_id' => $id,
            'status' => 2
        ]);

        return false;


    }
}
