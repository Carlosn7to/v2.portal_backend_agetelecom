<?php

namespace App\Console\Commands\Integrator\Schedule;

use App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Communicate\InfoOrder;
use App\Jobs\UpdateMirrorAniel;
use App\Models\Integrator\Aniel\Schedule\ImportOrder;
use Carbon\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Console\Command;

class Mirror extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aniel:mirror';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Espelhamento das ordens de serviço do Aniel';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $info = new InfoOrder();

        $info->__invoke();

        $startDate = Carbon::now()->subDays(10)->startOfDay();
        $uniqueDates = \App\Models\Integrator\Aniel\Schedule\Mirror::where('data_agendamento', '>=', $startDate)
            ->get(['data_agendamento'])
            ->map(function ($item) {
                return Carbon::parse($item->data_agendamento)->toDateString();
            })
            ->unique()
            ->values();

        $jobs = [];

        foreach ($uniqueDates as $date) {

            $ordersVoalle = ImportOrder::whereDate('data_agendamento', $date)->whereProtocolo('1186968')
                ->get(['protocolo', 'tipo_servico', 'data_agendamento', 'node as localidade', 'status_id', 'cliente_id'])->toArray();
            // Adicionar a job ao array de jobs
            $jobs[] = new UpdateMirrorAniel($ordersVoalle);
        }

        // Despacha os jobs em um batch
        \Bus::batch($jobs)->then(function (Batch $batch) {
            // Todos os jobs foram processados com sucesso
            \Log::info('Todos os jobs foram processados com sucesso.');
        })->catch(function (Batch $batch, \Throwable $e) {
            // Algum job falhou
            \Log::info('Algum job falhou.');
        })->finally(function (Batch $batch) {
            // Todos os jobs foram concluídos
            \Log::info('Todos os jobs foram concluídos.');
        })->dispatch();

        return 0;
    }
}
