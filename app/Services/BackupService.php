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
    protected $yesterday;

    public function __construct()
    {
        $this->yesterday = Carbon::yesterday()->format('Y-m-d');
    }

    /**
     * @throws CannotStartDump
     * @throws DumpFailed
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
            ->dumpToFile(storage_path().'/backups/'.$this->yesterday.'.sql');

        $path = storage_path().'/backups/'.$this->yesterday.'.sql';
        Storage::disk('s3')->put('SQL/'.substr($this->yesterday, 0, 7).'/'.$this->yesterday.'.sql', file_get_contents($path));
        Storage::delete($path);
    }

    /**
     * Request Log backup
     */
    public function request() {
        $path = storage_path().'/requests/'.$this->yesterday.'.log';
        Storage::disk('s3')->put('Request/'.substr($this->yesterday, 0, 7).'/'.$this->yesterday.'.log', file_get_contents($path));
        Storage::delete($path);
    }
}
