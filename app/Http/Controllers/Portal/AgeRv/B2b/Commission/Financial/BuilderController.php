<?php

namespace App\Http\Controllers\Portal\AgeRv\B2b\Commission\Financial;

use App\Helpers\Portal\AgeRv\B2b\Commission\Dashboard;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Classe Controller responsável por construir dados financeiros para comissões no portal.
 */
class BuilderController extends Controller
{
    /**
     * Armazena o período solicitado pelo usuário.
     * @var string
     */
    private $period;

    /**
     * Armazena os dados que serão retornados para o usuário.
     * @var array
     */
    private $data;

    /**
     * Construtor da classe.
     * Verifica se o usuário tem permissão para acessar a função.
     */
    public function __construct()
    {
        $this->middleware('portal.agerv.b2b.financial.access');
    }

    /**
     * Constrói dados financeiros para comissões.
     *
     * @param Request $request Os dados da requisição HTTP.
     * @return \Illuminate\Http\JsonResponse Os dados construídos.
     */
    public function builder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'period' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $this->actualPeriod = Carbon::parse($request->period)->addMonth();
        $this->subPeriod = Carbon::parse($request->period)->subMonth();
        $this->data = collect($this->getData());
        $this->dashboardData = (new Dashboard($this->data, $this->actualPeriod->subMonth()))->builder();


        return response()->json($this->dashboardData);
    }



    /**
     * Obtém os dados relevantes.
     */
    private function getData() : Collection
    {
        $query = $this->getQuery();
        $result = collect(\DB::connection('voalle')->select($query));

        return $result;
    }

    /**
     * Constrói a consulta SQL para obter os dados relevantes.
     *
     * @return string A consulta SQL.
     */
    private function getQuery(): string
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
                c.beginning_date
            FROM erp.contracts c
            INNER JOIN erp.people p ON p.id = c.client_id
            INNER JOIN erp.people p1 ON p1.id = c.seller_1_id
            INNER JOIN erp.people p2 ON p2.id = c.seller_2_id
            LEFT JOIN erp.contract_types ct ON ct.id = c.contract_type_id
            WHERE p2.id = 73512 and c.v_stage = \'Aprovado\'
            AND c.beginning_date >= \''.(clone $this->actualPeriod)->subMonth().'\' AND c.beginning_date < \''.$this->actualPeriod->format('Y-m-d').'\'
            ';

        return $query;
    }
}
