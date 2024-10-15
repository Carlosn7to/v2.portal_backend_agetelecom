<?php

namespace App\Http\Controllers\Portal\AgeReport\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\AgeReport\BuildReportRequest;
use App\Jobs\BuildingReportJob;
use App\Models\Portal\AgeReport\Management\Report;

class ReportsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $result = Report::all();

        return response()->json($result, 200);
    }

    public function buildingReport(BuildReportRequest $request)
    {
        $fields = $request->all();

        BuildingReportJob::dispatch($fields);

        return response()->json(['message' => 'Solicitação realizada com sucesso, o relatório está sendo criado.'], 200);


    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $result = Report::find($id);

        $result['filtros'] = json_decode($result->filtros, true);

        if(!$result) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        return response()->json($result, 200);
    }

    public function getColumns($reportId)
    {
        $report = Report::find($reportId);

        if(!str_contains($report->consulta, 'WITH')) {
            $queryWithoutWhere = preg_replace('/where.*/is', '', $report->consulta);

            $queryWithLimit = trim($queryWithoutWhere) . ' limit 1';
        } else {
            $queryWithLimit = trim($report->consulta) . ' limit 1';
        }



        $result = \DB::connection(mb_convert_case($report->conexao, MB_CASE_LOWER, 'utf8'))->select($queryWithLimit);

        if (count($result) > 0) {
            $keys = array_keys((array) $result[0]);

            $keys = array_map(function ($key) {
                return mb_convert_case($key, MB_CASE_TITLE, 'UTF-8');
            }, $keys);

            return $keys;
        }

        return [];

    }

}
