<?php namespace Darryldecode\Backend;

use Darryldecode\Backend\Base\Registrar\ComponentLoader;
use Darryldecode\Backend\Base\Registrar\Registrar;
use Darryldecode\Backend\Base\Registrar\WidgetLoader;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Darryldecode\Backend\Base\Services\Bus\Dispatcher;

class BackendServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/Base/Views', 'backend');
        $this->bootBackend();

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

        $this->publishes([
            __DIR__.'/Components/Auth/Views' => base_path('resources/views/backend/auth'),
        ], 'views');
    }

    /**
     * boot backend
     */
    public function bootBackend()
    {
        $disabled_components = [];
        $options = array(
            'disabled_components' => array_merge(
                $disabled_components,
                ($this->app['config']->get('backend.backend.disabled_components')) ? $this->app['config']->get('backend.backend.disabled_components') : []
            )
        );

        // load built-in components
        $componentLoader = new ComponentLoader(__DIR__.'/Components', new Filesystem(),$options);
        $builtInComponents = $componentLoader->getAvailableComponentInstances();

        // load custom components
        $componentLoader = new ComponentLoader(app_path().'/Backend/Components', new Filesystem(),$options);
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
        $backendRegistrar->initAddHeaderScripts();
        $backendRegistrar->initAddFooterScripts();

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
        $this->app->singleton('Darryldecode\Backend\Base\Services\Bus\Dispatcher', function ($app) {
            return new Dispatcher($app);
        });
        $this->app->alias(
            'Darryldecode\Backend\Base\Services\Bus\Dispatcher', 'Darryldecode\Backend\Base\Contracts\Bus\Dispatcher'
        );
    }
}