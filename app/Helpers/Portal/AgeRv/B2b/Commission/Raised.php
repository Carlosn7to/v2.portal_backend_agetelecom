<?php

namespace App\Helpers\Portal\AgeRv\B2b\Commission;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class Raised
{
    private $data;
    public $period;
    private $filteredData;
    private $invoices;
    private $invoicesActualPeriod;
    private $sumInvoicesActualPeriod;
    private $invoicesBeforePeriod;
    private $sumInvoicesBeforePeriod;

    public function __construct($data, $actualPeriod)
    {
        $this->data = new Collection($data);
        $this->period = $actualPeriod;
        $this->beforePeriod = (clone $actualPeriod)->subMonth();
    }

    public function builder()
    {
        $this->filteredData = $this->data->map(function ($item) {
            return collect($item)->only(['id'])->toArray();
        });

        $this->getInvoices();

//        $invoicesUniques = $this->invoices->filter(function($item) {
//            return $item['invoicesCount'] == 1;
//        });
//
//        return $invoicesUniques;

        return $this->invoices;
    }

    public function getInvoicesActualPeriod()
    {
        $this->builder();

        $this->period->addMonth();

        $this->invoicesActualPeriod = $this->invoices->map(function($item) {
            return collect($item['invoices'])->filter(function($invoice) {
                return Carbon::parse($invoice->receipt_date)->format('Y-m') == $this->period->format('Y-m');
            });
        });

    return $this->invoicesActualPeriod;
    }

    public function getSumInvoicesActualPeriod()
    {

        $this->sumInvoicesActualPeriod = $this->getInvoicesActualPeriod();


        $this->sumInvoicesActualPeriod = $this->sumInvoicesActualPeriod->map(function($item) {
            return $item->sum('total_amount');
        })->sum();

        return $this->sumInvoicesActualPeriod;
    }

    public function getInvoicesBeforePeriod()
    {

        $this->invoicesBeforePeriod = $this->invoices->map(function($item) {
            return collect($item['invoices'])->filter(function($invoice) {
                return Carbon::parse($invoice->receipt_date)->format('Y-m') == $this->beforePeriod->format('Y-m');
            });
        });

        return $this->invoicesBeforePeriod;

    }

    public function getSumInvoicesBeforePeriod()
    {
        $this->sumInvoicesBeforePeriod = $this->getInvoicesBeforePeriod();

        $this->sumInvoicesBeforePeriod = $this->sumInvoicesBeforePeriod->map(function($item) {
            return $item->sum('total_amount');
        })->sum();

        return $this->sumInvoicesBeforePeriod;
    }

    private function getInvoices()
    {
        $query = $this->getQuery();



        $this->invoices = collect(\DB::connection('voalle')->select($query));

        $uniqueContracts = $this->invoices->groupBy('contract_id');

        $this->invoices = $uniqueContracts->map(function($invoices) {
           return [
                'contract_id' => $invoices->first()->contract_id,
               'invoicesCount' => $invoices->count(),
               'invoices' => $invoices->toArray(),
           ];
        });



    }

    private function getQuery()
    {

        $contracts = $this->filteredData->map(function ($item) {
            return $item['id'];
        })->toArray();

        $query = '
        select
            frt2.contract_id,
            frt2.title,
            frt.receipt_date,
            frt.total_amount
        from erp.financial_receipt_titles frt
        left join erp.financial_receivable_titles frt2 on frt.financial_receivable_title_id = frt2.id
        where frt2.title like \'FAT%\' and frt2.contract_id in ('.implode(',', $contracts).')
        order by frt2.id desc';

        return $query;

    }
}
