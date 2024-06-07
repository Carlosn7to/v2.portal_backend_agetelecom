<?php

namespace App\Http\Controllers\Portal\AgeRv\B2b\Seller\Commission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BuilderController extends Controller
{
    protected $user;
    protected $response = [];

    public function __construct()
    {
        $this->user = auth('portal')->user();
        $this->getData();
        $this->response = [
            "nameSeller" => "",
            "infoContracts" => [
                [
                    "type" => "Link Dedicado",
                    "totalContracts" => 0,
                    "totalAmount" => 0,
                    "contracts" => [

                    ]
                ],
            ],
            "infoCommission" => [
                [
                    "type" => "Link Dedicado",
                    "commission" => [
                        "paid" => 0,
                        "toPay" => 0,
                        "expectToPaid" => 0,
                    ],
                    "rules" => [
                        [
                            "min" => 0,
                            "max" => 0,
                            "percent" => 0,
                        ]
                    ],
                    "additional" => [
                        "fidelity" => 0,
                        "metaAchieved" => 0,
                    ]
                ]
            ]

        ];
    }

    public function response()
    {
        return $this->response;


        $dedicated = $result->filter(function ($item) {
            return str_contains($item->title, 'Link Dedicado');
        });

        $interprise = $result->filter(function ($item) {
            return ! str_contains($item->title, 'Link Dedicado');
        });


        return response()->json([
            'dedicated' => $dedicated,
            'interprise' => $interprise
        ]);

        $dedicated = $dedicated->map(function ($item) {
            $item->amount = number_format($item->amount, 2, ',', '.');
            $item->approval_date = date('d/m/Y', strtotime($item->approval_date));
            return $item;
        });

    }

    private function getData() : void
    {
        $this->data = collect(\DB::connection('voalle')->select($this->getQuery()));
    }

    private function getQuery() : string
    {
        return <<<SQL
            SELECT
                c.id,
                ct.title,
                p.name AS "Cliente",
                c.amount,
                p1.name AS "Vendedor 1",
                p2.name AS "Vendedor 2",
                c.months_duration,
                c.approval_date
            FROM erp.contracts c
            INNER JOIN erp.people p ON p.id = c.client_id
            INNER JOIN erp.people p1 ON p1.id = c.seller_1_id
            INNER JOIN erp.people p2 ON p2.id = c.seller_2_id
            LEFT JOIN erp.contract_types ct ON ct.id = c.contract_type_id
            WHERE p1.email = 'suelen.santos@agetelecom.com.br'
        SQL;
    }

}
