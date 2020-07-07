<?php

namespace App\Providers;

use App\Models\Client;
use Illuminate\Support\ServiceProvider;
use Javoscript\MacroableModels\Facades\MacroableModels;

class ClientServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        MacroableModels::addMacro(Client::class, 'isRenewable', function () {
            return ! in_array($this->name, Client::BOT_TOKEN_RENEWAL_EXCEPTION);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
    }
}
