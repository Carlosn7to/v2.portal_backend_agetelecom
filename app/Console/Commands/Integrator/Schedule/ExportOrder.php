<?php

namespace App\Console\Commands\Integrator\Schedule;

use App\Http\Controllers\Integrator\Aniel\Schedule\_actions\Voalle\OrderSync;
use Illuminate\Console\Command;

class ExportOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aniel:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $syncOrder = new OrderSync();
        $syncOrder->__invoke();
    }
}
