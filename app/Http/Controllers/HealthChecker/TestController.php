<?php

namespace App\Http\Controllers\HealthChecker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function formatBytes($bytes, $decimals = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }

    public function index() {

        // Dados da CPU
        $cpuStats = $this->getCpuStats();

        // Coletar dados da RAM
        $freeOutput = shell_exec('free -b');
        preg_match_all('/\d+/', $freeOutput, $matches);
        $totalRam = $matches[0][1]; // Total de RAM em bytes
        $freeRam = $matches[0][2];  // RAM livre em bytes
        $usedRam = $totalRam - $freeRam; // RAM utilizada em bytes

        // Coletar dados do Disco
        $diskTotal = disk_total_space("/"); // Total de disco em bytes
        $diskFree = disk_free_space("/"); // Espaço livre em bytes

        return [
            'cpu' => $cpuStats,
            'ram' => [
                'total' => $this->formatBytes($totalRam),
                'used' => $this->formatBytes($usedRam),
            ],
            'disk' => [
                'total' => $this->formatBytes($diskTotal),
                'free' => $this->formatBytes($diskFree),
            ],
        ];
    }

    private function getCpuStats()
    {
        // Coletar dados da CPU total e utilizada
        $cpuStats = shell_exec('mpstat 1 1 | grep "Average:" | awk \'{print $3, $5}\'');
        preg_match_all('/\d+/', $cpuStats, $matches);
        $cpuIdle = $matches[0][0]; // Percentual de CPU ociosa
        $cpuUsed = 100 - $cpuIdle; // Percentual de CPU utilizada

        // Total de CPUs (não pode ser calculado diretamente em termos de tempo, mas pode ser considerado como o número de núcleos)
        $totalCpus = shell_exec('nproc');

        return [
            'total_cpus' => intval($totalCpus),
            'cpu' => [
                'used' => $cpuUsed,
                'idle' => $cpuIdle,
            ],
        ];

    }

}
