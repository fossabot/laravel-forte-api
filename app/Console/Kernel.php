<?php

namespace App\Console;

use App\Console\Commands\CleanRequestLog;
use App\Console\Commands\SyncXsollaItems;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\RenewalClientToken;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        RenewalClientToken::class,
        SyncXsollaItems::class,
        CleanRequestLog::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(RenewalClientToken::class)->hourly();
        $schedule->command(SyncXsollaItems::class)->dailyAt('02:00');
        $schedule->command(CleanRequestLog::class)->dailyAt('01:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
