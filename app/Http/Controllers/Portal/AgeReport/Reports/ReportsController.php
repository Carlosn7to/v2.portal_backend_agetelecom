<?php

namespace App\Http\Controllers\Portal\AgeReport\Reports;

use App\Events\SendNotificationsForUser;
use App\Exports\ReportDefaultExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\AgeReport\BuildReportRequest;
use App\Jobs\BuildingReportJob;
use App\Models\Portal\AgeReport\Assignment\Assignment;
use App\Models\Portal\AgeReport\Management\Report;
use App\Models\Portal\AgeReport\Management\UserRole;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = UserRole::whereUsuarioId(auth('portal')->user()->id)->get();

        if($roles->count() == 0) {
            return response()->json(['message' => 'Você não tem permissão para acessar relatórios'], 403);
        }

        $roleIds = $roles->pluck('relatorios_liberados')
            ->map(function ($item) {
                return json_decode($item);
            })
            ->flatten()
            ->filter()
            ->toArray();
        $result = Report::whereIn('id', $roleIds)->get(['id', 'nome', 'descricao', 'tipo']);

        return response()->json($result, 200);
    }

    public function buildingReport(BuildReportRequest $request)
    {
        $this->data = $request->all();
        $this->userId = auth('portal')->user()->id;

        $report = Report::find($this->data['reportId']);

        if($this->data['command'] == 'unique') {

            $query = $report->consulta;

            $assigmentReport = Assignment::create([

                'relatorio_id' => $this->data['reportId'],
                'usuario_id' => $this->userId,
                'tipo' => 'unico',
                'caminho_arquivo' => 'pendente',
                'status' => 'pendente'
            ]);


            if (str_contains($query, 'WHERE')) {
                $query .= " AND DATE({$this->data['dateFilter']['columnFilter']}) BETWEEN '{$this->data['dateFilter']['startDate']}' AND '{$this->data['dateFilter']['endDate']}'";
            } else {
                $query .= " WHERE DATE({$this->data['dateFilter']['columnFilter']}) BETWEEN '{$this->data['dateFilter']['startDate']}' AND '{$this->data['dateFilter']['endDate']}'";
            }

            $result = \DB::connection(mb_convert_case($report->conexao, MB_CASE_LOWER, 'utf8'))->select($query);

            if (count($result) > 0) {
                $keys = array_keys((array) $result[0]);

                $assigmentReport->update([
                    'status' => 'processando'
                ]);

                $keys = array_map(function ($key) {
                    return str_replace(' ', '_', mb_convert_case($key, MB_CASE_LOWER, 'UTF-8'));
                }, $keys);



                foreach ($result as &$value) {
                    // Converter o objeto para um array
                    $valueArray = (array)$value;

                    foreach ($valueArray as $k => $v) {
                        $k = str_replace(' ', '_', mb_convert_case($k, MB_CASE_LOWER, 'UTF-8'));

                        return [$k, $keys[0]];

                        // Verifica se a chave não está nos cabeçalhos válidos
                        if (!in_array($k, $keys)) {
                            echo $k;
                            unset($valueArray[$k]); // Remove a chave não permitida
                        }
                    }

                    // Converte de volta para objeto
                    $value = (object)$valueArray;
                }



                return $result;


                $archiveName = str_replace(' ', '_', $report->nome) . '_' . Carbon::now()->format('d_m_Y__H-i-s') . '.'. $this->data['options']['typeArchive'];

                if($this->data['options']['typeArchive'] == 'xlsx') {
                    \Maatwebsite\Excel\Facades\Excel::store(new ReportDefaultExport($result, $headers), $archiveName, 'publicReport');
                    $path = storage_path("app/public/agereport/reports/{$archiveName}");

                    $assigmentReport->update([
                        'caminho_arquivo' => $path,
                        'status' => 'concluido'
                    ]);

                    $content = [
                        'type' => 'notification',
                        'command' => 'report-download',
                        'title' => 'Seu relatório está pronto!',
                        'message' => 'Clique abaixo para realizar o download',
                        'report' => [
                            'assignment_id' => $assigmentReport->id,
                            'name' => $report->nome.'.xlsx'
                        ]
                    ];

                    broadcast(new SendNotificationsForUser($this->userId, $content));
                }

            }


        }

//        BuildingReportJob::dispatch($fields, auth('portal')->user()->id);

        return response()->json(['message' => 'Solicitação realizada com sucesso, o relatório está sendo criado.'], 200);


    }

    public function downloadReport($assignmentId)
    {

        $assignment = Assignment::find($assignmentId);
        $report = Report::find($assignment->relatorio_id);
        if(!$assignment) {
            return response()->json(['message' => 'Solicitação não encontrada'], 404);
        }

        if($assignment->usuario_id != auth('portal')->user()->id) {
            return response()->json(['message' => 'Você não tem permissão para baixar este arquivo'], 403);
        }

        $filePath = $assignment->caminho_arquivo; // Substitua pelo caminho do seu arquivo
        $fileName = $report->nome.'.xlsx'; // Nome com o qual o arquivo será baixado


        if (file_exists($filePath)) {
            return response()->download($filePath, $fileName)->setStatusCode(200);
        }


        return response()->json(['message' => 'Download realizado com sucesso'], 200);
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
