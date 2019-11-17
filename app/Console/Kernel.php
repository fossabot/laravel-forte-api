<?php

namespace App\Console;

use App\Console\Commands\BackupDB;
use App\Console\Commands\CleanRequestLog;
use App\Console\Commands\DepositUserPoint;
use App\Console\Commands\RenewalClientToken;
use App\Console\Commands\SyncXsollaItems;
use Illuminate\Console\Scheduling\Schedule;
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
        BackupDB::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(BackupDB::class)->daily();
        $schedule->command(CleanRequestLog::class)->daily();
        $schedule->command(RenewalClientToken::class)->twiceDaily(2, 14);
        $schedule->command(SyncXsollaItems::class)->dailyAt('02:00');
        $schedule->command(DepositUserPoint::class)->monthlyOn(1, '00:30');
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
