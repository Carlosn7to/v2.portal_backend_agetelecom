<?php

namespace App\Http\Controllers\Portal\AgeRv\B2b\Seller\Commission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BuilderController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = auth('portal')->user();


    }

    public function response()
    {
        $seller = 'SUELEN CARVALHO DOS SANTOS';

        $query = '
            select
                c.id,
                ct.title,
                p.name as "Cliente",
                c.amount,
                p1.name as "Vendedor 1",
                p2.name as "Vendedor 2",
                c.months_duration,
                c.approval_date
            from erp.contracts c
            inner join erp.people p on p.id = c.client_id
            inner join erp.people p1 on p1.id = c.seller_1_id
            inner join erp.people p2 on p2.id = c.seller_2_id
            left join erp.contract_types ct on ct.id = c.contract_type_id
            where c.contract_type_id in (11,23,21,20,14,22,10)
            and p1."name" = \''.$seller.'\'';

        $result = collect(\DB::connection('voalle')->select($query));

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

        foreach($dedicated as $key => $value) {

        }


    }
}
