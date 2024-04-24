<?php

namespace App\Http\Controllers\Portal\BI\Voalle\Financial\B2B;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GoodPayerController extends Controller
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
            SELECT
            c.id as Contrato,
            c.date as "Data criação",
            frt.client_paid_date as "Data pagamento",
            frt.receipt_date as "Data recebimento",
            c.v_stage as Estágio,
            c.v_status as Status,
            money(frt.amount) as Valor,
            money(frt.discount_value) as "Valor do desconto",
            money(frt.increase_amount) as Juros,
            money(frt.total_amount) as "Valor total pago",
            frt2.created as "Data criação fatura",
            frt2.competence as Competência,
            pf.title as "Tipo de pagamento",
            CASE
               when c.discount_use_contract = 2 then 'Nao aplicar desconto'
               when c.discount_use_contract = 1 then 'Sim, conforme o Contrato'
               when c.discount_use_contract = 0 then 'Sim, conforme Tipo de Cobrança'
            end as "Utiliza desconto",
            frt2.billet_printed as Baixado,
            frt2.original_expiration_date as "Vencimento original",
            frt2.title as Título,
            c.collection_day as "Dia vencimento",
            fn.title as "Natureza financeira",
            (select p.name from erp.people p where p.id = c.seller_1_id) as "vendedor 1",
            (select p.name from erp.people p where p.id = c.seller_2_id) as "vendedor 2",
            CASE
                WHEN EXTRACT(MONTH FROM frt2.competence) = 1 THEN 'jan'
                WHEN EXTRACT(MONTH FROM frt2.competence) = 2 THEN 'fev'
                WHEN EXTRACT(MONTH FROM frt2.competence) = 3 THEN 'mar'
                WHEN EXTRACT(MONTH FROM frt2.competence) = 4 THEN 'abr'
                WHEN EXTRACT(MONTH FROM frt2.competence) = 5 THEN 'mai'
                WHEN EXTRACT(MONTH FROM frt2.competence) = 6 THEN 'jun'
                WHEN EXTRACT(MONTH FROM frt2.competence) = 7 THEN 'jul'
                WHEN EXTRACT(MONTH FROM frt2.competence) = 8 THEN 'ago'
                WHEN EXTRACT(MONTH FROM frt2.competence) = 9 THEN 'set'
                WHEN EXTRACT(MONTH FROM frt2.competence) = 10 THEN 'out'
                WHEN EXTRACT(MONTH FROM frt2.competence) = 11 THEN 'nov'
                WHEN EXTRACT(MONTH FROM frt2.competence) = 12 THEN 'dez'
            END AS "Competencia Nome do Mes",
            EXTRACT(MONTH FROM frt2.competence) as "Competencia Mes",
            EXTRACT(YEAR FROM frt2.competence) as "Competencia Ano"
            from erp.contracts c
            right join erp.financial_receivable_titles frt2  on frt2.contract_id = c.id
            right join erp.financial_receipt_titles frt on frt.financial_receivable_title_id  = frt2.id
            left join erp.payment_forms pf on frt.payment_form_id = pf.id
            left join erp.financers_natures fn on fn.id = frt2.financer_nature_id
            inner join erp.contract_types ct on ct.id = c.contract_type_id
            where frt2.deleted is false
            and frt2."type" = 2
            and frt2.financer_nature_id notnull
            and frt2.bill_title_id is null
            and frt.deleted is false
            and frt.finished is false
            and frt2.title_loss = 0
            and ct.title ilike '%PJ%'
            SQL;

    }
}
