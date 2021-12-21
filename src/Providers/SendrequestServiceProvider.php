<?php

namespace ItCpp\Sendrequest\Providers;

use Illuminate\Support\ServiceProvider;

class SendrequestServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
