<?php

namespace Laget\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Laget\NugetPackage;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'Laget\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot(Router $router)
    {
        $router->model('packages', 'Laget\NugetPackage', function($key) {
            $s = explode('@', $key, 2);
            $id = $s[0];
            if(count($s) < 2)
            {
                return NugetPackage::where('is_absolute_latest_version', true)
                    ->where('package_id', $id)
                    ->firstOrFail();
            }
            else
            {
                return NugetPackage::where('is_absolute_latest_version', true)
                    ->where('package_id', $id)
                    ->where('version', $s[1])
                    ->firstOrFail();
            }
        });

        parent::boot($router);
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function map(Router $router)
    {
        $router->group(['namespace' => $this->namespace], function ($router) {
            require app_path('Http/routes.php');
        });
    }
}
