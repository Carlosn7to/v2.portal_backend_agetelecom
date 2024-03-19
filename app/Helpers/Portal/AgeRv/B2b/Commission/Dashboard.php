<?php

namespace App\Helpers\Portal\AgeRv\B2b\Commission;

use Carbon\Carbon;

class Dashboard
{
    private $data;
    private $dashboardData;
    public $period;

    public function __construct($data, $period)
    {
        $this->data = $data;
        $this->period = $period;
    }

    public function builder()
    {

        $this->actualData = $this->data->filter(function ($item) {
            return Carbon::parse($item->beginning_date)->format('Y-m') == $this->period->format('Y-m');
        });

        $this->beforeData = $this->data->filter(function ($item) {
            return Carbon::parse($item->beginning_date)->format('Y-m') != $this->period->format('Y-m');
        });

        $this->getDashboardData();

        return $this->dashboardData;
    }


    /**
     * Obtém os dados do dashboard.
     */
    public function getDashboardData()
    {
        $raised = new Raised($this->data, $this->period);
        $commission = new CommissionSeller($this->data, $this->period);
        $sellers = new Sellers($this->data, $this->period);

        $this->dashboardData = [
            'clients' => [
                'total' => [
                    'actual' => $this->actualData->count(),
//                    'before' => $this->beforeData->count()
                ],
                'period' => [
                    'actual' => (clone $this->period)->format('Y-m'),
//                    'before' => $this->beforeData->count()
                ],
//                'extract' => $this->actualData
            ],
            'raised' => [
                'total' => [
                    'actual' => $raised->getSumInvoicesActualPeriod(),
//                    'before' => $raised->getSumInvoicesBeforePeriod()
                ],
                'period' => [
                    'actual' => (clone $raised->period)->format('Y-m'),
//                    'before' => $raised->getInvoicesBeforePeriod()
                ]
            ],
            'sellers' => $sellers->getListSellers(),
            'topSellers' => [],
            'lastNews' => [],
            'commission' => [
                'total' => [
                    'actual' => 10,
//                    'before' => $commission->getSumCommissionBeforePeriod()
                ],
//                'beforeMonthPercent' => 0,
            ],
            'profit' => [
                'total' => [
                    'actual' => 10,
//                    'before' => $commission->getSumCommissionBeforePeriod()
                ],
//                'beforeMonthPercent' => 0,
            ],
        ];


        return $this->dashboardData;
    }



}
