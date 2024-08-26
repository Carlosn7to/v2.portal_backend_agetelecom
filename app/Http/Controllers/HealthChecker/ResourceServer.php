<?php

namespace App\Http\Controllers\HealthChecker;

use App\Models\HealthChecker\App;
use App\Models\HealthChecker\AppResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ResourceServer
{

    public function __invoke()
    {
        $this->response();
    }

    /**
     * Formata bytes em uma unidade legível.
     */
    private function formatBytes($bytes, $decimals = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor));
    }

    /**
     * Formata valores percentuais.
     */
    private function formatPercentage($value, $decimals = 1) {
        return number_format($value, $decimals);
    }

    /**
     * Coleta e retorna estatísticas de CPU.
     */
    private function getCpuStats() {
        // Coletar dados da CPU para todos os núcleos
        $cpuStats = shell_exec("mpstat -P ALL 1 1 | awk '/^[0-9]/ {print $12}'");

        // Converter string em array, ignorar linhas vazias e a linha de cabeçalho
        $cpuIdleArray = array_map('floatval', explode("\n", trim($cpuStats)));
        $cpuIdleArray = array_filter($cpuIdleArray, fn($value) => is_numeric($value) && $value >= 0 && $value <= 100);

        // Total de CPUs (contar o número de valores válidos)
        $totalCpus = count($cpuIdleArray);

        // Calcular a média do percentual de CPU ociosa
        $cpuIdle = $totalCpus > 0 ? array_sum($cpuIdleArray) / $totalCpus : 0; // Percentual de CPU ociosa
        $cpuUsed = 100 - $cpuIdle; // Percentual de CPU utilizada

        return [
            'total_cpus' => $totalCpus,
            'cpu' => [
                'used' => $this->formatPercentage($cpuUsed),
                'idle' => $this->formatPercentage($cpuIdle),
            ],
        ];
    }

    /**
     * Coleta e retorna estatísticas de RAM.
     */
    private function getRamStats() {
        // Coletar dados da RAM
        $freeOutput = shell_exec('free -b');
        $lines = explode("\n", trim($freeOutput));

        // Encontra a linha com os dados da memória
        $memLine = isset($lines[1]) ? $lines[1] : '';
        preg_match_all('/(\d+)\s+(\d+)\s+(\d+)/', $memLine, $matches);

        $totalRam = intval($matches[1][0]); // Total de RAM em bytes
        $usedRam = intval($matches[2][0]);  // RAM usada em bytes

        return [
            'total' => $totalRam,
            'used' => $usedRam,
        ];
    }

    /**
     * Coleta e retorna estatísticas do disco.
     */
    private function getDiskStats() {
        // Coletar dados do Disco
        $diskTotal = disk_total_space("/"); // Total de disco em bytes
        $diskFree = disk_free_space("/"); // Espaço livre em bytes

        return [
            'total' => $diskTotal,
            'free' => $diskFree,
            'used' => $diskTotal - $diskFree, // Espaço usado
        ];
    }

    /**
     * Insere os dados no banco de dados.
     */
    private function insertStatsIntoDatabase($cpuStats, $ramStats, $diskStats, $hour_minute) {
        $appResources = new AppResource();

        $appResources = $appResources->whereDate('created_at', Carbon::now()->format('Y-m-d'))
            ->where('hora_minuto', $hour_minute)
            ->firstOrCreate(
                [
                    'hora_minuto' => $hour_minute,
                ],
                [
                    'aplicacao_id' => 1,
                    'cpu_nucleos_totais' => $cpuStats['total_cpus'],
                    'cpu_uso' => $cpuStats['cpu']['used'],
                    'cpu_disponivel' => $cpuStats['cpu']['idle'],
                    'ram_total' => $ramStats['total'],
                    'ram_uso' => $ramStats['used'],
                    'disco_total' => $diskStats['total'],
                    'disco_uso' => $diskStats['used'],
                ]
            );



    }

    /**
     * Coleta, formata e insere todas as estatísticas do sistema.
     */
    public function response()  : void
    {

        $hour_minute = Carbon::now()->format('H:i');

        \Log::error('Hora minuto do início ' . $hour_minute);

        $cpuStats = $this->getCpuStats();
        $ramStats = $this->getRamStats();
        $diskStats = $this->getDiskStats();

        $this->insertStatsIntoDatabase($cpuStats, $ramStats, $diskStats, $hour_minute);
        \Log::error('Hora minuto do fim ' . $hour_minute);


//        return response()->json([
//            'cpu' => $cpuStats,
//            'ram' => $ramStats,
//            'disk' => $diskStats,
//        ]);
    }

    public function getAnalyticResourcesLastHour()
    {

        $lastUsage = AppResource::whereDate('created_at', Carbon::now()->format('Y-m-d'))
            ->where('hora_minuto', '>=', Carbon::now()->subHour()->format('H:i'))
            ->orderBy('hora_minuto', 'asc')
            ->get();

        return response()->json($lastUsage, 200);

    }

    public function getSpaceDiskAvailable()
    {
        $apps = App::all();

        foreach($apps as &$app) {
            $lastUsage = AppResource::whereAplicacaoId($app->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if($lastUsage) {
                $app->disco_total = $lastUsage->disco_total;
                $app->disco_uso = $lastUsage->disco_uso;
            }
        }

        return response()->json($apps, 200);
    }

    public function getUpTimeServer()
    {

    }
}
