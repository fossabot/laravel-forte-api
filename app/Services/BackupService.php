<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Exceptions\CannotStartDump;
use Spatie\DbDumper\Exceptions\DumpFailed;

class BackupService
{
    /**
     * @var string
     */
    protected $today;

    /**
     * @throws DumpFailed
     * @throws CannotStartDump
     */
    public function database()
    {
        if (! file_exists(storage_path('backups'))) {
            mkdir(storage_path('backups'), 0777);
        }

        MySql::create()
            ->setDbName(config('database.connections.mysql.database'))
            ->setUserName(config('database.connections.mysql.username'))
            ->setPassword(config('database.connections.mysql.password'))
            ->dumpToFile(storage_path().'/backups/'.$this->today.'.sql');

        $yymm = Carbon::now()->format('Y-m');
        $yymmdd = Carbon::now()->format('Y-m-d');
        $path = storage_path().'/backups/'.$this->today.'.sql';
        Storage::disk('s3')->put('SQL/'.$yymm.'/'.$yymmdd.'.sql', file_get_contents($path));
    }
}
