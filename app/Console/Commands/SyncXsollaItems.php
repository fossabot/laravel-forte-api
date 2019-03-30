<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\XsollaAPIService;

class SyncXsollaItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xsolla:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Solla Items from Forte Items';

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
     * @param XsollaAPIService $xsollaAPI
     * @return mixed
     */
    public function handle(XsollaAPIService $xsollaAPI)
    {
        $xsollaAPI->syncItems();
    }
}
