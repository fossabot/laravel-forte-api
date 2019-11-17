<?php

namespace App\Console\Commands;

use App\Http\Controllers\PointController;

class DepositUserPoint
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deposit:point';

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
