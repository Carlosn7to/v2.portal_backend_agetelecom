<?php

namespace App\Http\Controllers\Portal\AgeRv\B2b\Commission\Financial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Provisory2BuilderController extends Controller
{
    public function __construct()
    {
        $this->middleware('portal.agerv.b2b.financial.access');
    }

    public function builder(Request $request)
    {
        $this->data = (new ProvisoryBuilderController())->builder($request->period);


       $this->levelCommission();

       $this->getCommission();
       return $this->data;

    }

    private function levelCommission()
    {
        $level = [
            'dedicated' => [
                [
                    'level' => 1,
                    'rule' => [1,2],
                    'percentInvoice' => 0.4
                ],
                [
                    'level' => 2,
                    'rule' => [3,4],
                    'percentInvoice' => 0.5
                ],
                [
                    'level' => 3,
                    'rule' => [5,9],
                    'percentInvoice' => 0.6
                ],
                [
                    'level' => 4,
                    'rule' => [10, 100],
                    'percentInvoice' => 0.7
                ],
            ],
            'business' => [
                [
                    'level' => 1,
                    'rule' => [1, 10],
                    'percentInvoice' => 0.3
                ],
                [
                    'level' => 2,
                    'rule' => [11, 24],
                    'percentInvoice' => 0.5
                ],
                [
                    'level' => 3,
                    'rule' => [25, 200],
                    'percentInvoice' => 0.6
                ],
            ],
        ];

        foreach($this->data as &$sellerData) { // Usando referência (&) para modificar diretamente o array original

            foreach ($level['dedicated'] as $type => $levels) {

                if($sellerData['contracts']['dedicated'] >= $levels['rule'][0] && $sellerData['contracts']['dedicated'] <= $levels['rule'][1]) {
                    $sellerData['percentInvoiceDedicated'] = $levels['percentInvoice'];
                    break; // Sai do loop interno assim que a condição é satisfeita
                } else {
                    $sellerData['percentInvoiceDedicated'] = 0;
                }
            }

            foreach ($level['business'] as $type => $levels) {

                if($sellerData['contracts']['business'] >= $levels['rule'][0] && $sellerData['contracts']['business'] <= $levels['rule'][1]) {
                    $sellerData['percentInvoiceBusiness'] = $levels['percentInvoice'];
                    break; // Sai do loop interno assim que a condição é satisfeita
                } else {
                    $sellerData['percentInvoiceBusiness'] = 0;
                }
            }
        }





    }

    private function getCommission()
    {

        foreach ($this->data as &$sellerData) { // Agora corretamente usando referência (&)

            foreach ($sellerData['invoices'] as &$invoice) {


                if ($invoice['invoice'] != null) {

                    if (str_contains(mb_convert_case($invoice['title'], MB_CASE_LOWER, 'utf8'), 'dedicado')) {
                        $invoice['invoice']->commission = $invoice['invoice']->total_amount * $sellerData['percentInvoiceDedicated'];
                        $sellerData['commissionDedicated'] += $invoice['invoice']->commission;
                        $invoice['invoice']->percentCommission = $sellerData['percentInvoiceDedicated'];
                    } else {
                        $invoice['invoice']->commission = $invoice['invoice']->total_amount * $sellerData['percentInvoiceBusiness'];
                        $sellerData['commissionBusiness'] += $invoice['invoice']->commission;
                        $invoice['invoice']->percentCommission = $sellerData['percentInvoiceBusiness'];
                    }
                }
            }
            unset($invoice); // Desfaz a referência no final de cada loop interno
        }
        unset($sellerData); // Desfaz a referência no final do loop externo


    }
}
