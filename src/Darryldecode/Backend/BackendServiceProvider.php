<?php namespace Darryldecode\Backend;

use Darryldecode\Backend\Base\Registrar\ComponentLoader;
use Darryldecode\Backend\Base\Registrar\Registrar;
use Darryldecode\Backend\Base\Registrar\WidgetLoader;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class BackendServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     * @param Router $router \Illuminate\Contracts\Http\Kernel
     */
    public function boot(Router $router)
    {
        $this->loadViewsFrom(__DIR__.'/Base/Views', 'backend');
        $this->bootBackend();

        $router->middleware(
            'backend.guest',
            'Darryldecode\Backend\Base\Middleware\RedirectIfAuthenticated'
        );

        $router->middleware(
            'backend.authenticated',
            'Darryldecode\Backend\Base\Middleware\Authenticate'
        );

        $this->publishes([
            __DIR__.'/Public/backend/cb' => public_path('darryldecode/backend/cb'),
            __DIR__.'/Public/backend/vendor' => public_path('darryldecode/backend/vendor'),
        ], 'public');

        $this->publishes([
            __DIR__.'/Config' => config_path('backend'),
        ], 'config');

        $this->publishes([
            __DIR__.'/Database/Migrations' => database_path('migrations'),
            __DIR__.'/Database/Seeders' => database_path('seeds'),
        ], 'migrations');
    }

    /**
     * boot backend
     */
    public function bootBackend()
    {
        // load built-in components
        $componentLoader = new ComponentLoader(__DIR__.'/Components', new Filesystem());
        $builtInComponents = $componentLoader->getAvailableComponentInstances();

        // load custom components
        $componentLoader = new ComponentLoader(app_path().'/Backend/Components', new Filesystem());
        $customComponents = $componentLoader->getAvailableComponentInstances();

        // add those loaded components to backend registrar
        $backendRegistrar = new Registrar();

        $backendRegistrar->addComponent(array(
            $builtInComponents
        ));
        $backendRegistrar->addComponent(array(
            $customComponents
        ));

        // this should be in-order
        $backendRegistrar->initRoutes();
        $backendRegistrar->initViews();
        $backendRegistrar->initNavigation();
        $backendRegistrar->initPermissions();

        // load views
        foreach($backendRegistrar->getViewsPaths() as $view)
        {
            $this->loadViewsFrom($view['dir'], $view['namespace']);
        }

        // load built-in widgets
        $builtInWidgetsLoader = new WidgetLoader(__DIR__.'/Widgets', new Filesystem());
        $customWidgetsLoader  = new WidgetLoader(app_path().'/Backend/Widgets', new Filesystem());

        // add widgets
        $backendRegistrar->addWidget($builtInWidgetsLoader->getAvailableWidgetInstances());
        $backendRegistrar->addWidget($customWidgetsLoader->getAvailableWidgetInstances());

        $this->app['backend'] = $backendRegistrar;
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

    }
}