<?php namespace Darryldecode\Backend;

use Darryldecode\Backend\Base\Registrar\ComponentLoader;
use Darryldecode\Backend\Base\Registrar\Registrar;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class BackendServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
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