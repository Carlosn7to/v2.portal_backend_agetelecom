<?php

namespace App\Http\Controllers\HealthChecker;

use App\Http\Controllers\Controller;
use App\Models\HealthChecker\AppResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{

    public function index()
    {

        $resourceWatcher = new ResourceServer();

        $resourceWatcher->response();

        Log::error('Monitoramento de recursos do servidor finalizado.');

        return 12;


        $performance = new AppResource();

        $startTime = Carbon::now()->subMinutes(60);
        $endTime = Carbon::now();

        $data = $performance->whereBetween('created_at', [$startTime, $endTime])
            ->orderBy('created_at')
            ->get();

        $result = [];
        $uniqueMinutes = [];

        foreach ($data as $value) {
            $minute = Carbon::parse($value->created_at)->format('Y-m-d H:i'); // Formato 'YYYY-MM-DD HH:MM' para garantir unicidade por minuto
            if (!array_key_exists($minute, $uniqueMinutes)) {
                $uniqueMinutes[$minute] = $value;
            }
        }

        foreach ($uniqueMinutes as $minute => $item) {
            $item->hora_minuto = $minute;
            unset($item->created_at);
            unset($item->updated_at);
            $result[] = $item;
        }

        $resultFormatted = $this->formatResourceData(collect($result));


        return $resultFormatted;
    }

    private function formatResourceData($data) {
        return $data->map(function ($item) {
            $item->ram_total = $this->formatBytes($item->ram_total);
            $item->ram_uso = $this->formatBytes($item->ram_uso);
            $item->disco_total = $this->formatBytes($item->disco_total);
            $item->disco_uso = $this->formatBytes($item->disco_uso);
            return $item;
        });
    }


    private function formatBytes($bytes, $decimals = 2) {
        // Certifique-se de que $bytes é um valor numérico
        $bytes = (float) $bytes;

        if ($bytes <= 0) {
            return '0 B';
        }

        $size = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB'];
        $factor = floor((log($bytes) / log(1024)));

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . $size[$factor];
    }

}
