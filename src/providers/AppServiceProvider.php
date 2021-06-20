<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/13/20, 6:07 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if($this->app->runningInConsole())
        {
            if (iauth('database.migrations.include', true)) $this->loadMigrationsFrom(iauth_path('database/migrations'));
        }
        View::addLocation(iauth_path('resources/views'));
        $this->mergeConfigFrom(iauth_path('config/iauth.php'), 'ilaravel.main.iauth');
    }

    public function register()
    {
        parent::register();
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
