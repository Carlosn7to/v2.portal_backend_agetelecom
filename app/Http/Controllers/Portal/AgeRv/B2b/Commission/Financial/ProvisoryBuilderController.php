<?php

namespace App\Http\Controllers\Portal\AgeRv\B2b\Commission\Financial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProvisoryBuilderController extends Controller
{
    public function builder($period, $typeCollaborator = '')
    {

        $this->period = Carbon::parse($period);
        $this->typeCollaborator = $typeCollaborator;


        $this->getData();
        $this->getSellers();
        $this->groupDataSellers();

        return $this->groupDataSellers;

    }

    private function groupDataSellers() : void
    {
        $result = [];

           foreach ($this->sellers as $seller) {
                $data = $this->data->where('seller', $seller);
                $result[] = [
                    'seller' => $seller,
                    'contracts' => $this->getContractsByType($data),
                    'meta' => $this->getMeta($seller),
                    'invoices' => $this->getInvoices($data),
                    'contracts_data' => $data,
                    'commissionDedicated' => 0,
                    'commissionBusiness' => 0,

                ];
            }


           $this->groupDataSellers = $result;
    }

    private function getInvoices($data)
    {
        $uniqueContracts = collect($data)->unique('id');



        $contracts = $uniqueContracts->map(function ($item) {
            return collect($item)->only(['id', 'amount', 'approval_date', 'title']);
        });

        $invoices = $contracts->map(function ($contract) {
            return [
                'contract_id' => $contract['id'],
                'title' => $contract['title'],
                'approval_date' => $contract['approval_date'],
                'invoice' => $this->getInvoice($contract),
                'amount' => $this->getContractAmountBeforeDiscount($contract),
            ];
        });

        return $invoices;
    }

    private function getContractAmountBeforeDiscount($contract)
    {

        if($this->invoices == null) {
            return [
                'brute' => $contract['amount'],
                'liquid' => $contract['amount'],
                'month_reference' => null
            ];
        }



        $query = 'select
        cev.month_year AS month_year,
        c.amount AS amount,
        sum(cev.v_total_amount) AS total_amount
        from erp.contracts c
        left join erp.contract_eventual_values cev on cev.contract_id = c.id
        left join erp.people p on p.id = c.client_id
        where cev.month_year = \''.Carbon::parse($this->invoices->competence)->startOfMonth()->toDateString().'\' and c.id = '.$contract['id'].' and cev.deleted is false group by c.id, cev.month_year';

        $result = collect(\DB::connection('voalle')->select($query));

        $amount = [
            'brute' => $contract['amount'],
            'liquid' => $result->first() ? $contract['amount'] + $result->first()->total_amount : $contract['amount'],
            'month_reference' => $result->first() ? $result->first()->month_year : null
        ];


        return $amount;


    }

    private function getInvoice($contractInfo)
    {

        $query = '
        select
            frt2.contract_id,
            frt2.title,
            frt2.competence,
            frt.receipt_date,
            frt.total_amount
        from erp.financial_receipt_titles frt
        left join erp.financial_receivable_titles frt2 on frt.financial_receivable_title_id = frt2.id
        where frt2.title like \'FAT%\' and frt2.contract_id = ' . $contractInfo['id'] . '
        order by frt2.id desc';

        $this->invoices = collect(\DB::connection('voalle')->select($query));

        if($this->invoices->count() > 1) {
            $this->invoices = $this->invoices->first();
        } else if($this->invoices->count() > 0 ) {

            if($this->invoices->first()->total_amount >= $contractInfo['amount']) {
                $this->invoices = $this->invoices->first();
            } else {
                $this->invoices = null;
            }

        } else {
            $this->invoices = null;
        }

        return $this->invoices;

    }

    private function getSellers() : void
    {
        $this->sellers = collect($this->data->unique('seller')->pluck('seller'));
    }

    private function getContractsByType($data)
    {

        $result = [
            'dedicated' => 0,
            'business' => 0
        ];

        foreach($data as $contract) {

            if(str_contains(mb_convert_case($contract->title, MB_CASE_LOWER, 'utf8'), 'dedicado')) {
                $result['dedicated']++;
            } else {
                $result['business']++;
            }

        }

        return $result;




    }

    private function getMeta($name) : array
    {

        $meta = [];

        $mcv = [
            'Milena Lopes de Lima',
            'Julia de Sousa Rosa',
            'SUELEN CARVALHO DOS SANTOS'
        ];

        foreach ($mcv as $m => $mcvName) {
            if (strcasecmp(trim($name), trim($mcvName)) == 0) {
                $meta = [
                    'dedicated' => 4,
                    'business' => 25
                ];
                break;
            } else {
                $meta = [
                    'dedicated' => 4,
                    'business' => 10
                ];
            }
        }

        return $meta;
    }

    private function getData() : void
    {
        $result = collect(\DB::connection('voalle')->select($this->getQuery()));
        $this->data = $result;
    }

    private function getQuery()
    {



        $query = '
            SELECT
                c.id,
                ct.title,
                p.name AS "client",
                c.amount,
                p1.name AS "seller",
                p2.name AS "supervisor",
                c.months_duration,
                c.approval_date
            FROM erp.contracts c
            INNER JOIN erp.people p ON p.id = c.client_id
            INNER JOIN erp.people p1 ON p1.id = c.seller_1_id
            INNER JOIN erp.people p2 ON p2.id = c.seller_2_id
            LEFT JOIN erp.contract_types ct ON ct.id = c.contract_type_id
            WHERE p2.id = 73512 and c.v_stage = \'Aprovado\'
            AND c.approval_date >= \''.clone($this->period).'\' and c.approval_date < \''.clone($this->period->addMonth()->startOfMonth()).'\'
            ';

        if($this->typeCollaborator === 'seller') {$query .= 'and p1.email = \'daniela.ernesto@agetelecom.com.br\'';}

        return $query;

    }
}
