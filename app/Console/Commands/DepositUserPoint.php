<?php

namespace App\Console\Commands;

use App\Http\Controllers\PointController;
use Illuminate\Console\Command;

class DepositUserPoint extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:staff-points';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deposit User (Staffs/Supports) give points.';

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
     * @param PointController $pointController
     * @return mixed
     */
    public function handle(PointController $pointController)
    {
        $pointController->schedule();
    }
}
