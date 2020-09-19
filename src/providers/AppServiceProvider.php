<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/13/20, 6:07 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if($this->app->runningInConsole())
        {
            if (iauth('database.migrations.include', true)) $this->loadMigrationsFrom(iauth_path('database/migrations'));
        }
    }

    public function register()
    {
        parent::register();
        $this->mergeConfigFrom(iauth_path('config/iauth.php'), 'ilaravel.iauth');
        Config::set([
            'ilaravel.auth.login' => false,
            'ilaravel.auth.register' => false,
            'ilaravel.auth.auto_register' => false,
            'ilaravel.auth.forgot' => false,
            'ilaravel.auth.logout' => false,
            'ilaravel.auth.get' => false,
            'ilaravel.auth.update' => false,
        ]);
    }
}
