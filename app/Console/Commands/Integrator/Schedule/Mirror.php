<?php

namespace App\Console\Commands\Integrator\Schedule;

use App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Communicate\InfoOrder;
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
        $infoOrder = new InfoOrder();

        $infoOrder->__invoke();
    }
}
