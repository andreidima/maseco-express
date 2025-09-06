<?php

namespace App\Console;

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
        // $schedule->command('model:prune', [
        //     '--model' => OfertaCursa::class,
        // ])
        // ->dailyAt('22:23')         // pick a low-traffic time
        // ->withoutOverlapping();

        // Andrei
        $schedule->command('model:prune', [
            '--model' => \App\Models\OfertaCursa::class,
            '--pretend' => true, // just log what it WOULD delete
        ])
        ->everyMinute()
        ->evenInMaintenanceMode()
        ->timezone('Europe/Bucharest')
        ->appendOutputTo(storage_path('logs/prune.log'));
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
