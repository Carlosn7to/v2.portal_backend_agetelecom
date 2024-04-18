<?php

namespace App\Http\Controllers\Integrator\Voalle\Billets;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            "client_id" => env('VOALLE_API_CLIENT_ID'),
            "client_secret" => env('VOALLE_API_CLIENT_SECRET'),
            "syndata" => env('VOALLE_API_SYNDATA')
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


        // Verifique se a requisição foi bem-sucedida (código de status 200)
        if ($responseBillet->getStatusCode() == 200) {


            // Obtenha o conteúdo do PDF
            $pdfContent = $responseBillet->getBody()->getContents();

            $options = [
                'ACL' => 'public-read' // Define a ACL como public-read
            ];

            Storage::disk('aws_digitro')->put('boletos/' . 'boleto_' . $id . '.pdf', $pdfContent, $options);

            return true;
        }

        return false;


    }
}
