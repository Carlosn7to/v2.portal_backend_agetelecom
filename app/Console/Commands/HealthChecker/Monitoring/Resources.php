<?php

namespace App\Console\Commands\HealthChecker\Monitoring;

use App\Http\Controllers\HealthChecker\ResourceServer;
use Illuminate\Console\Command;

class Resources extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server:resources';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitoramento de recursos do servidor';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $resourceWatcher = new ResourceServer();
        $resourceWatcher->__invoke();
    }
}
