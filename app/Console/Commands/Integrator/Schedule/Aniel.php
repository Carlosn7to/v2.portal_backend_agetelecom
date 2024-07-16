<?php

namespace App\Console\Commands\Integrator\Schedule;

use App\Http\Controllers\Integrator\Aniel\Schedule\BuilderController;
use Illuminate\Console\Command;

class Aniel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:aniel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inserção de ordem de serviço no aniel';

    /**
     * Execute the console command.
     */
    public function handle()
    {


    }
}
