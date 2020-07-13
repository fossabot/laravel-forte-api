<?php

namespace App\Console\Commands;

use App\Http\Controllers\CleanLogController;
use Illuminate\Console\Command;

class CleanRequestLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:clean-log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean Request Log';

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
     * @param CleanLogController $cleanLog
     * @return mixed
     */
    public function handle(CleanLogController $cleanLog)
    {
        $cleanLog->clean();
    }
}
