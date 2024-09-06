<?php

namespace App\Http\Controllers\Portal\AgeReport\Management\Reports;

use App\Http\Controllers\Controller;
use App\Models\Portal\AgeReport\Management\Report;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reports = Report::all();
        return response()->json($reports, 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(),[
            'typeReport' => 'required:string',
            'name' => 'required|string',
            'description' => 'required|string',
            'area' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $report = new Report();

        if($request->typeReport == 'report') {
            $validator = \Validator::make($request->all(),[
                'consult' => 'required|string',
                'database' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 401);
            }

            $report = $report->firstOrCreate(
                ['nome' => $request->name, 'tipo' => $request->typeReport],
                [
                    'nome' => $request->name,
                    'area' => $request->area,
                    'tipo' => 'relatorio',
                    'descricao' => $request->description,
                    'consulta' => $request->consult,
                    'conexao' => $request->database,
                    'filtros' => json_encode($request->filters),
                    'criado_por' => auth('portal')->user()->id,
                    'atualizado_por' => auth('portal')->user()->id
                ]
            );

            if($report->wasRecentlyCreated){
                return response()->json(['message' => 'Relatório criado com sucesso!'], 201);
            }

            return response()->json(['message' => 'Já existe um relatório com esse nome.'], 200);


        }

        if($request->typeReport == 'dashboard') {
            $validator = \Validator::make($request->all(),[
                'link' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 401);
            }

            $report = $report->firstOrCreate(
                ['nome' => $request->name, 'tipo' => $request->typeReport],
                [
                    'nome' => $request->name,
                    'area' => $request->area,
                    'tipo' => 'dashboard',
                    'descricao' => $request->description,
                    'iframe' => $request->link,
                    'criado_por' => auth('portal')->user()->id,
                    'atualizado_por' => auth('portal')->user()->id
                ]
            );

            if($report->wasRecentlyCreated){
                return response()->json(['message' => 'Dashboard criado com sucesso!'], 201);
            }

            return response()->json(['message' => 'Já existe um dashboard com esse nome.'], 200);
        }





    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
