<?php

namespace App\Console;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        // $schedule->command('portal:users:create')->dailyAt('07:00');
        $schedule->command('rule:billing:sending')
            ->dailyAt('08:35');

        $schedule->command('tracking:services')
            ->everyMinute()->withoutOverlapping();

        $schedule->command('aniel:capacity')
            ->everyFiveMinutes()->withoutOverlapping();

        $schedule->command('aniel:export')
            ->everyFiveMinutes()->withoutOverlapping();

        $schedule->command('aniel:mirror')
            ->everyFiveMinutes()->withoutOverlapping();

        $schedule->command('aniel:clear-sendings')
            ->everyMinute()->withoutOverlapping();

        $schedule->command('server:resources')
            ->everyMinute()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
