<?php

namespace App\Services;

use Carbon\Carbon;
use App\Mail\BackupDB;
use Illuminate\Support\Facades\Mail;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Exceptions\DumpFailed;
use Spatie\DbDumper\Exceptions\CannotStartDump;

class BackupService
{
    /**
     * @var string
     */
    protected $today;

    /**
     * BackupService constructor.
     */
    public function __construct() {
        $this->today = Carbon::now()->format('Y-m-d');
    }

    /**
     * @throws DumpFailed
     * @throws CannotStartDump
     */
    public function database() {
        if (! file_exists(storage_path('backups'))) {
            mkdir(storage_path('backups'), 0777);
        }

        MySql::create()
            ->setDbName(config('database.connections.mysql.database'))
            ->setUserName(config('database.connections.mysql.username'))
            ->setPassword(config('database.connections.mysql.password'))
            ->dumpToFile(storage_path() . '/backups/'. $this->today . '.sql');

//        (new \App\Http\Controllers\DiscordNotificationController)->backupSQL();

        Mail::send(new BackupDB());
    }
}
