<?php

namespace App\Jobs;

use App\Events\SendNotificationsForUser;
use App\Exports\ReportDefaultExport;
use App\Models\Portal\AgeReport\Assignment\Assignment;
use App\Models\Portal\AgeReport\Management\Report;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BuildingReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data;
    private $userId;

    /**
     * Create a new job instance.
     */
    public function __construct($data, $userId)
    {
        $this->data = $data;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

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
                    return mb_convert_case($key, MB_CASE_TITLE, 'UTF-8');
                }, $keys);

                $headers = $keys;

                if ($this->data['options']['columns'][0] != 'all') {
                    foreach ($this->data['options']['columns'] as $column) {
                        // Remove o header indesejado
                        $headers = array_filter($headers, function ($header) use ($column) {
                            return $header != $column;
                        });

                        // Remove a coluna correspondente de cada linha em $result
                        foreach ($result as &$row) {
                            if (isset($row[$column])) {
                                unset($row[$column]);
                            }
                        }
                    }
                }


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

    }
}
