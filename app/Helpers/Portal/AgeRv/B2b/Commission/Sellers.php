<?php

namespace App\Helpers\Portal\AgeRv\B2b\Commission;

class Sellers
{

    public function __construct($data, $period)
    {
        $this->data = $data;
        $this->period = $period;
    }

    public function builder()
    {
        return 10;
    }

    public function getListSellers()
    {

        $this->data = $this->data->groupBy('seller');


        $this->listSellers = $this->data->map(function($sales) {
            return [
                'seller' => $sales->first()->seller,
                'sales' => [
                    'dedicated' => [
                        'meta' => 4,
                        'total' => $sales->filter(function($item) {
                          return str_contains($item->title, 'Link Dedicado');
                      })->count(),
                      'extract' => $sales->filter(function($item) {
                          return str_contains($item->title, 'Link Dedicado');
                      }),
                    ],
                    'interprise' => [
                        'meta' => 10,
                        'total' => $sales->filter(function($item) {
                            return ! str_contains($item->title, 'Link Dedicado');
                        })->count(),
//                        'extract' => $sales->filter(function($item) {
//                            return ! str_contains($item->title, 'Link Dedicado');
//                        }),
                    ],
                ],
                'invoices' => [
                    'dedicated' => $this->getInvoices($sales->filter(function($item) {
                        return str_contains($item->title, 'Link Dedicado');
                    })),
                    'interprise' => $this->getInvoices($sales->filter(function($item) {
                        return ! str_contains($item->title, 'Link Dedicado');
                    })),
                ]
            ];
        });


        return $this->listSellers;
    }


    private function getInvoices($contracts)
    {

        $contractsId = [];

        foreach($contracts as $contract) {
            $contractsId[] = $contract->id;
        }


        if(empty($contractsId)) {
            return [];
        }

        $query = '
        select
            frt2.contract_id,
            frt2.title,
            frt.receipt_date,
            frt.total_amount,
            frt2.competence
        from erp.financial_receipt_titles frt
        left join erp.financial_receivable_titles frt2 on frt.financial_receivable_title_id = frt2.id
        where frt2.title like \'FAT%\' and frt2.contract_id in ('.implode(',', $contractsId).')
        and frt2.competence = \'2024-02-01\'
        order by frt.total_amount asc';

        $result = collect(\DB::connection('voalle')->select($query));


        $contractGroup = $result->groupBy('contract_id');

        $invoices = $contractGroup->map(function($invoices) {
            return [
                'contract_id' => $invoices->first()->contract_id,
                'invoices' => $invoices->toArray(),
            ];
        });

        $sumInvoices = $invoices->map(function($item) {
            return collect($item['invoices'])->sum('total_amount');
        })->sum();

        return [
            'sumTotal' => $sumInvoices,
            'sumTotalInovites' => $invoices->count(),
            'extract' => $invoices
        ];


    }
}
