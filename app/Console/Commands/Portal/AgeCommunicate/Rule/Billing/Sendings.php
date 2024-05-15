<?php

namespace App\Console\Commands\Portal\AgeCommunicate\Rule\Billing;

use App\Http\Controllers\Portal\AgeCommunicate\BillingRule\BuilderBillingRuleController;
use Illuminate\Console\Command;

class Sendings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rule:billing:sending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envio da regra de cobrança.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $builder = (new BuilderBillingRuleController())->__invoke();

    }
}
