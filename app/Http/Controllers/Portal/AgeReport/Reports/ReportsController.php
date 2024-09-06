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

        if(!$result) {
            return response()->json(['message' => 'Report not found'], 404);
        }



        return response()->json($result, 200);
    }

}
