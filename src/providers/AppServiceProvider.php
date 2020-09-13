<?php

namespace iLaravel\iAuth\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(iauth_path('config/iauth.php'), 'ilaravel.iauth');

        if($this->app->runningInConsole())
        {
            if (iauth('database.migrations.include', true)) $this->loadMigrationsFrom(iauth_path('database/migrations'));
        }
    }

    public function register()
    {
        parent::register();
    }
}
