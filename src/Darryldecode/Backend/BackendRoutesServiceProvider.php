<?php namespace Darryldecode\Backend;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class BackendRoutesServiceProvider extends ServiceProvider {

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot(Router $router)
    {
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
        // register component routes
        foreach($this->app['backend']->getRoutes() as $route)
        {
            $router->group(['prefix' => config('backend.backend.base_url'), 'namespace' => $route['namespace']], function($router) use ($route)
            {
                require $route['dir'];
            });
        }
    }
}