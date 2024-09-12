<?php

namespace App\Http\Controllers\Portal\AgeReport\Reports;

use App\Http\Controllers\Controller;
use App\Models\Portal\AgeReport\Management\Report;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

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

        $result = \DB::connection(mb_convert_case($report->conexao, MB_CASE_LOWER, 'utf8'))->select($report->consulta." limit 1");

        if (count($result) > 0) {
            $keys = array_keys((array) $result[0]);

            // Converte todas as chaves para case_title
            $keys = array_map(function ($key) {
                return mb_convert_case($key, MB_CASE_TITLE, 'UTF-8');
            }, $keys);

            return $keys;
        }

        return [];

    }

}
