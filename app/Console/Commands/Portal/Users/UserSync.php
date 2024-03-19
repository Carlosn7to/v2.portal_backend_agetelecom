<?php

namespace App\Console\Commands\Portal\Users;

use Illuminate\Console\Command;


class UserSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'portal:users:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizar usuários no portal espelhando o AD.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userSync = new \app\Routines\Portal\Users\UserSync();

        $userSync->__invoke();

        $userSync->sendingReport();




    }
}
