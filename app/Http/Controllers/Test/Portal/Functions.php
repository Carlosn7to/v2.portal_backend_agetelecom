<?php

namespace App\Http\Controllers\Test\Portal;

use App\Helpers\Portal\Mail\Notification\Builder;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Management\User\UserController;
use App\Mail\Portal\AgeCommunicate\Rule\Billing\SendBilling;
use App\Models\Portal\User\User;
use App\Routines\Portal\Users\UserSync;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class Functions extends Controller
{

    private $user;

    public function __construct()
    {
//        $this->middleware('portal.master')->only('index');
    }

    public function index()
    {
        set_time_limit(20000000000);



        $data = collect(\DB::connection('voalle')->select($this->getQuery()));

        $data = $data->map(function ($item) {
//             $item->email = 'carlos.neto@agetelecom.com.br';
             $item->days_until_expiration = -5;

             return $item;
        });

        return $this->sendEmail($data);

    }

    private function getQuery(): string
    {
        $query = '
            SELECT
                c.id AS "contract_id",
                p.email AS "email",
                p.v_name AS "name",
                frt.document_amount,
                p.tx_id,
                CASE
                    WHEN p.cell_phone_1 IS NOT NULL THEN p.cell_phone_1
                    ELSE p.cell_phone_2
                END AS "phone",
                frt.typeful_line AS "barcode",
                frt.expiration_date AS "expiration_date",
                frt.competence AS "competence",
                case
                    when frt.expiration_date > current_date then -(frt.expiration_date - current_date)
                    else (current_date - frt.expiration_date)
                end as "days_until_expiration",
                frt.id as "frt_id"
            FROM erp.contracts c
            LEFT JOIN erp.people p ON p.id = c.client_id
            LEFT JOIN erp.financial_receivable_titles frt ON frt.contract_id = c.id
            WHERE
                c.v_stage = \'Aprovado\'
                and c.v_status != \'Cancelado\'
                AND frt.competence >= \'2023-05-01\'
                AND frt.deleted IS FALSE
                AND frt.finished IS FALSE
                AND frt.title LIKE \'%FAT%\'
                and frt.p_is_receivable is true
                and frt.typeful_line is not null
                and c.id = 34263
            limit 100
            ';

        return $query;

    }


    private function sendEmail($dataEmail)
    {
        $templates = $this->getTemplates();
        $sendings = ['success' => [], 'count' => 0, 'error' => []];

        $data = collect($dataEmail)->unique('email');
        $accessToken = $this->getAccessToken();

        foreach ($data as $value) {
            $billetPath = $this->fetchBillet($value, $accessToken);
            if ($billetPath) {
                $this->sendAppropriateEmails($value, $templates, $sendings, $billetPath);
                @unlink($billetPath); // Clean up after sending
            }
        }

        return $templates; // For auditing or logging
    }

    private function getTemplates()
    {
        return [
            [
                'rule' => -5,
                'view' => 'pre_expiration_5',
                'subject' => 'Lembrete - Vencimento da sua fatura Age Telecom em 5 dias',
                'sendings' => 0,
                'extract' => [],
                'title' => 'Pré vencimento 5 dias'
            ],
            [
                'rule' => -4,
                'view' => 'pre_expiration_4',
                'subject' => 'Lembrete - Vencimento da sua fatura Age Telecom em 4 dias',
                'sendings' => 0,
                'extract' => [],
                'title' => 'Pré vencimento 4 dias'
            ],
        ];
    }

    private function getAccessToken()
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

        $access = json_decode($response->getBody()->getContents());

        return $response->getStatusCode() == 200 ? $access->access_token : null;
    }

    private function fetchBillet($customerData, $accessToken)
    {
        $client = new Client();


        $response = $client->get('https://erp.agetelecom.com.br:45715/external/integrations/thirdparty/GetBillet/' . $customerData->frt_id, [
            'headers' => [
                "Authorization" => "Bearer $accessToken",
            ]
        ]);

        if ($response->getStatusCode() === 200) {
            $pdfContent = $response->getBody();

            $filePath = storage_path('app/portal/agecommunicate/rule/billing/boleto.pdf');

            // Salve o arquivo no caminho especificado
            file_put_contents($filePath, $pdfContent);

            return $filePath;
        }

        return null;
    }

    private function sendAppropriateEmails($customerData, &$templates, &$sendings, $billetPath)
    {
        $dateFormatted = Carbon::now()->isoFormat('D [de] MMMM [de] YYYY');

        foreach ($templates as &$template) {
            if ($this->matchRule($customerData, $template['rule'])) {
                // Implement email sending logic here. Example:
                \Mail::mailer('fat')->to($customerData->email)->send(new SendBilling($template['view'], $template['subject'], $customerData, $billetPath));

                $template['sendings']++;
                $sendings['success'][] = ['template' => $template['view'], 'client' => $customerData];
                $sendings['count']++;
                break; // Assuming one email per customer per run
            }
        }
    }

    private function matchRule($customerData, $rule)
    {
        if (is_array($rule)) {
            return in_array($customerData->days_until_expiration, $rule);
        }

        return $customerData->days_until_expiration == $rule;
    }

}

