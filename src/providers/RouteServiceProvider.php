<?php

namespace iLaravel\iAuth\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        parent::boot();
    }

    public function register()
    {
        parent::register();
    }
    public function map(Router $router)
    {
        if (iauth('routes.api.status', true)) $this->apiRoutes($router);
    }

    public function apiRoutes(Router $router)
    {
        $router->group([
            'namespace' => '\iLaravel\iAuth\iApp\Http\Controllers\API',
            'prefix' => 'api',
            'middleware' => 'api'
        ], function ($router) {
            require_once(iauth_path('routes/api.php'));
        });
    }
}
