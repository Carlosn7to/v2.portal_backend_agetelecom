<?php

namespace App\Http\Controllers\HealthChecker;

use App\Models\HealthChecker\AppResource;

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

        // Converter string em array
        $cpuIdleArray = array_map('floatval', explode("\n", trim($cpuStats)));

        // Filtrar valores válidos (não vazios)
        $cpuIdleArray = array_filter($cpuIdleArray, fn($value) => $value !== '');

        // Calcular a média do percentual de CPU ociosa
        $cpuIdle = array_sum($cpuIdleArray) / count($cpuIdleArray); // Percentual de CPU ociosa
        $cpuUsed = 100 - $cpuIdle; // Percentual de CPU utilizada

        // Total de CPUs
        $totalCpus = count($cpuIdleArray);

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
    private function insertStatsIntoDatabase($cpuStats, $ramStats, $diskStats) {
        $appResources = new AppResource();

        $appResources->create([
            'aplicacao_id' => 1,
            'cpu_nucleos_totais' => $cpuStats['total_cpus'],
            'cpu_uso' => $cpuStats['cpu']['used'],
            'cpu_disponivel' => $cpuStats['cpu']['idle'],
            'ram_total' => $ramStats['total'],
            'ram_uso' => $ramStats['used'],
            'disco_total' => $diskStats['total'],
            'disco_uso' => $diskStats['used'],
        ]);

    }

    /**
     * Coleta, formata e insere todas as estatísticas do sistema.
     */
    public function response()  : void
    {
        $cpuStats = $this->getCpuStats();
        $ramStats = $this->getRamStats();
        $diskStats = $this->getDiskStats();

        $this->insertStatsIntoDatabase($cpuStats, $ramStats, $diskStats);

//        return response()->json([
//            'cpu' => $cpuStats,
//            'ram' => $ramStats,
//            'disk' => $diskStats,
//        ]);
    }
}
