<?php

namespace App\Console\Commands;

use App\Routines\Portal\Tracking\AgeAtende\TrackingAgeAtende;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Nette\Utils\Random;

class trackingServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracking:services';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitoramento dos serviços';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $ageAtende = (new TrackingAgeAtende())->getStatus();
    }
}
