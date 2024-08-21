<?php

namespace App\Http\Controllers\HealthChecker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestController extends Controller
{
    /**
     * Formata bytes em uma unidade legível.
     */
    private function formatBytes($bytes, $decimals = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f %s", $bytes / pow(1024, $factor), $units[$factor]);
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
        // Coletar dados da CPU
        $cpuStats = shell_exec('top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/"');
        $cpuIdle = trim($cpuStats);
        $cpuUsed = 100 - $cpuIdle; // Percentual de CPU utilizada

        // Total de CPUs
        $totalCpus = shell_exec('nproc');

        return [
            'total_cpus' => intval($totalCpus),
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
        $freeOutput = shell_exec('free -h');
        $lines = explode("\n", trim($freeOutput));

        // Encontra a linha com os dados da memória
        $memLine = isset($lines[1]) ? $lines[1] : '';
        preg_match_all('/([\d.]+)([A-Za-z]+)/', $memLine, $matches);

        $totalRam = $matches[1][0] ?? '0'; // Total de RAM
        $totalUnit = $matches[2][0] ?? 'B'; // Unidade da memória
        $usedRam = $matches[1][1] ?? '0';   // RAM usada
        $usedUnit = $matches[2][1] ?? 'B';  // Unidade da memória

        return [
            'total' => $totalRam . ' ' . $totalUnit, // Unidade da memória
            'used' => $usedRam . ' ' . $usedUnit,   // Unidade da memória
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
            'total' => $this->formatBytes($diskTotal),
            'free' => $this->formatBytes($diskFree),
        ];
    }

    /**
     * Retorna todas as estatísticas do sistema.
     */
    public function index() {
        return response()->json([
            'cpu' => $this->getCpuStats(),
            'ram' => $this->getRamStats(),
            'disk' => $this->getDiskStats(),
        ]);
    }

}
