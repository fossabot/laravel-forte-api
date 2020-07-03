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
        if ($this->isStorageDirectoryNotExists()) {
            $this->makeStorageDirectory();
        }

        $path = storage_path().'/backups/'.$this->yesterday.'.sql';

        $this->dumpDatabaseTo($path);

        $this->saveFileToS3AndDeleteFromLocal($path);
    }

    /**
     * Request Log backup.
     */
    public function request()
    {
        $path = storage_path().'/requests/'.$this->yesterday.'.log';

        Storage::disk('s3')->put('Request/'.$this->getLogFilePath(), file_get_contents($path));

        Storage::delete($path);
    }

    private function isStorageDirectoryNotExists(): bool
    {
        return ! file_exists(storage_path('backups'));
    }

    private function makeStorageDirectory(): void
    {
        mkdir(storage_path('backups'), 0777);
    }

    private function dumpDatabaseTo($file): void
    {
        MySql::create()
            ->setDbName(config('database.connections.mysql.database'))
            ->setUserName(config('database.connections.mysql.username'))
            ->setPassword(config('database.connections.mysql.password'))
            ->dumpToFile($file);
    }

    private function saveFileToS3AndDeleteFromLocal($file): void
    {
        $this->saveFileToS3($file);
        $this->deleteFile($file);
    }

    private function saveFileToS3($file): void
    {
        Storage::disk('s3')->put('SQL/'.$this->getSqlFilePath(), file_get_contents($file));
    }

    private function getSqlFilePath()
    {
        return $this->getYear($this->yesterday).'-'.$this->getMonth($this->yesterday).'/'.$this->yesterday.'.sql';
    }

    private function deleteFile($file): void
    {
        Storage::delete($file);
    }

    private function getLogFilePath()
    {
        return $this->getYear($this->yesterday).'-'.$this->getMonth($this->yesterday).'/'.$this->yesterday.'.log';
    }

    public function getYear(string $date)
    {
        return substr($date, 0, 4);
    }

    public function getMonth(string $date)
    {
        return substr($date, 5, 2);
    }
}
