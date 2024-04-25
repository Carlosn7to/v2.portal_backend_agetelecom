<?php

namespace App\Http\Controllers\Portal\BI\Voalle\Financial\B2B;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReceipTitlesController extends Controller
{

    private $token = '1813826105dfcb87c112d47b103570778d0ff89ca44e0adbf83671b42b1cf43c';


    public function __construct()
    {
        $this->middleware('portal.bi.access.ip');
    }

    public function builderForBI(Request $request)
    {


        if($this->token !== $request->token){
            return response()->json(['error' => 'Unauthorized'], 401);
        }


        $this->getData();

        return $this->data;
    }

    private function getData()
    {
        $this->data = \DB::connection('voalle')->select($this->getQuery());
    }

    private function getQuery() : string
    {
        return<<<SQL
            select
                f.title_amount as "valor titulo",
                fn.title as "natureza financeira",
                f.expiration_date as "vencimento",
                frt.client_paid_date as "data do pagamento",
                frt.receipt_date as "data do recebimento",
                f.original_expiration_date as "vencimento original",
                f.competence as "competencia",
                case
                    when f.deleted = 'f' then 'nao'
                    when f.deleted = 't' then 'sim'
                end as "excluido",
                f.contract_id as "numero de contrato",
                (select p.name from erp.people p where p.id = c.seller_1_id) as "vendedor 1",
                (select p.name from erp.people p where p.id = c.seller_2_id) as "vendedor 2",
                CASE
                WHEN EXTRACT(MONTH FROM f.competence) = 1 THEN 'jan'
                WHEN EXTRACT(MONTH FROM f.competence) = 2 THEN 'fev'
                WHEN EXTRACT(MONTH FROM f.competence) = 3 THEN 'mar'
                WHEN EXTRACT(MONTH FROM f.competence) = 4 THEN 'abr'
                WHEN EXTRACT(MONTH FROM f.competence) = 5 THEN 'mai'
                WHEN EXTRACT(MONTH FROM f.competence) = 6 THEN 'jun'
                WHEN EXTRACT(MONTH FROM f.competence) = 7 THEN 'jul'
                WHEN EXTRACT(MONTH FROM f.competence) = 8 THEN 'ago'
                WHEN EXTRACT(MONTH FROM f.competence) = 9 THEN 'set'
                WHEN EXTRACT(MONTH FROM f.competence) = 10 THEN 'out'
                WHEN EXTRACT(MONTH FROM f.competence) = 11 THEN 'nov'
                WHEN EXTRACT(MONTH FROM f.competence) = 12 THEN 'dez'
                    END AS "Competencia Nome do Mes",
                EXTRACT(MONTH FROM f.competence) as "Competencia Mes",
                EXTRACT(YEAR FROM f.competence) as "Competencia Ano"
                from erp.financial_receivable_titles f
                inner join erp.financers_natures fn on fn.id = f.financer_nature_id
                left join erp.contracts c on c.id = f.contract_id
                left join erp.financial_receipt_titles frt on frt.financial_receivable_title_id = f.id
                where f.title ilike '%fat%'
                and c.contract_type_id in (5,11,18,23,21,20,2,14,22,16,17,10)
            SQL;

    }
}
