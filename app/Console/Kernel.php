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
        // ✅ Sitemap auto: chaque week-end (samedi + dimanche) à 03:00
        $schedule->command('gls:generate-sitemap')
            ->weekends()
            ->at('03:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/sitemap-schedule.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
