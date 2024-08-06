<?php

namespace App\Console\Commands\Integrator\Schedule;

use App\Models\Integrator\Aniel\Schedule\CommunicateMirror;
use Illuminate\Console\Command;

class ClearSendings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aniel:clear-sendings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpar as comunicações que foram enviadas em dias anteriores para reagendamentos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $communicateMirror = CommunicateMirror::whereStatusAniel(0)
            ->get();

        if($communicateMirror) {

            foreach($communicateMirror as $communicate) {
                $communicate->envio_deslocamento = 0;
                $communicate->save();
            }

        }
    }
}
