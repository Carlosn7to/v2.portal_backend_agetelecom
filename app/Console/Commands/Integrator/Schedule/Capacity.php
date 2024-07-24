<?php

namespace App\Console\Commands\Integrator\Schedule;

use App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Management\DashboardSchedule;
use App\Http\Controllers\Integrator\Aniel\Schedule\BuilderController;
use Illuminate\Console\Command;

class Capacity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aniel:capacity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizar capacidade de atendimento do aniel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
//        $syncCapacity = new BuilderController();
//        $syncCapacity->__invoke();

        $syncBrokenOrders = new DashboardSchedule();
        $syncBrokenOrders->__invoke();
    }
}
