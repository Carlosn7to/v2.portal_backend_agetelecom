<?php

namespace App\Jobs;

use App\Exports\ReportDefaultExport;
use App\Models\Portal\AgeReport\Management\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BuildingReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $report = Report::find($this->data['reportId']);

        if($this->data['command'] == 'unique') {

            $query = $report->consulta;

            if (str_contains($query, 'WHERE')) {
                $query .= " AND DATE({$this->data['dateFilter']['columnFilter']}) BETWEEN '{$this->data['dateFilter']['startDate']}' AND '{$this->data['dateFilter']['endDate']}'";
            } else {
                $query .= " WHERE DATE({$this->data['dateFilter']['columnFilter']}) BETWEEN '{$this->data['dateFilter']['startDate']}' AND '{$this->data['dateFilter']['endDate']}'";
            }

            $result = \DB::connection(mb_convert_case($report->conexao, MB_CASE_LOWER, 'utf8'))->select($query);

            if (count($result) > 0) {
                $keys = array_keys((array) $result[0]);

                $keys = array_map(function ($key) {
                    return mb_convert_case($key, MB_CASE_TITLE, 'UTF-8');
                }, $keys);

                $headers = $keys;
                $archiveName = str_replace(' ', '_', $report->nome) . '_' . date('Y-m-d_H-i-s') . '.'. $this->data['options']['typeArchive'];

                if($this->data['options']['typeArchive'] == 'xlsx') {
                    \Maatwebsite\Excel\Facades\Excel::store(new ReportDefaultExport($result, $headers), $archiveName, 'public_agereport_reports');
                    $path = storage_path("app/public/{$archiveName}");
                }

            }


        }

    }
}
