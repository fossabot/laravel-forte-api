<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;
use Spatie\DbDumper\Exceptions\CannotStartDump;
use Spatie\DbDumper\Exceptions\DumpFailed;

class BackupDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:backup-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup DB';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param BackupService $backup
     * @return mixed
     * @throws DumpFailed
     * @throws CannotStartDump
     */
    public function handle(BackupService $backup)
    {
        $backup->database();
    }
}
