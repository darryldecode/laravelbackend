<?php namespace Darryldecode\Backend;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class BackendRoutesServiceProvider extends ServiceProvider {

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        // register component routes
        foreach($this->app['backend']->getRoutes() as $route)
        {
            $this->app['router']->group(['prefix' => config('backend.backend.base_url'), 'namespace' => $route['namespace'], 'middleware' => ['web']], function($router) use ($route)
            {
                require $route['dir'];
            });
        }
    }
}