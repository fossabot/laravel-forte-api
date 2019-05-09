<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\DiscordNotificationController;

class DeployNotificationFromAWS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aws:codedeploy {status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy Notification From AWS CodeDeploy';

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
     * @param DiscordNotificationController $discordNotification
     * @return mixed
     */
    public function handle(DiscordNotificationController $discordNotification)
    {
        $discordNotification->deploy($this->argument('status'));
    }
}
