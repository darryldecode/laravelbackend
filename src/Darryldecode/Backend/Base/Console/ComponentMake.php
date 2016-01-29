<?php

namespace Darryldecode\Backend\Base\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ComponentMake extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backend:component-create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new backend custom component.';

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Execute the console command.
     *
     * @param Filesystem $filesystem
     * @return mixed
     */
    public function handle(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $scaffoldsPath = __DIR__.'/../Etc/scaffolds/';

        // component info
        $componentTitle         = $this->ask('Enter component name');
        $componentDescription   = $this->ask('Enter component description');
        $componentIcon          = $this->ask('Enter component icon (ex. fa fa-home)');
        $componentNamespace     = $this->formatToComponentNamespace($componentTitle);
        $componentUrl           = $this->formatToComponentUrl($componentTitle);

        $backendPath    = app_path().'/Backend';
        $componentPath  = $backendPath.'/Components/'.$componentNamespace;

        if( ! $this->filesystem->isDirectory($backendPath) )
        {
            $this->filesystem->makeDirectory($backendPath);
        }

        if( ! $this->filesystem->isDirectory($backendPath.'/Components') )
        {
            $this->filesystem->makeDirectory($backendPath.'/Components');
        }

        if( $this->filesystem->isDirectory($componentPath) )
        {
            $this->error('Component already exist.');
            exit;
        }

        // create necessary component directories
        $this->filesystem->makeDirectory($componentPath);
        $this->filesystem->makeDirectory($componentPath.'/Controllers');
        $this->filesystem->makeDirectory($componentPath.'/Views');

        // process scaffolds
        $componentScaffold  = $this->filesystem->get($scaffoldsPath.'Component');
        $controllerScaffold = $this->filesystem->get($scaffoldsPath.'Controller');
        $viewScaffold       = $this->filesystem->get($scaffoldsPath.'view');
        $routesScaffold     = $this->filesystem->get($scaffoldsPath.'routes');

        $this->comment(PHP_EOL.'Creating component...'.PHP_EOL);

        $componentScaffold = str_replace('{{componentNamespace}}',$componentNamespace,$componentScaffold);
        $componentScaffold = str_replace('{{componentTitle}}',$componentTitle,$componentScaffold);
        $componentScaffold = str_replace('{{componentDescription}}',$componentDescription,$componentScaffold);
        $componentScaffold = str_replace('{{componentIcon}}',$componentIcon,$componentScaffold);
        $componentScaffold = str_replace('{{componentUrl}}',$componentUrl,$componentScaffold);
        $this->filesystem->put($componentPath.'/Component.php',$componentScaffold);

        $controllerScaffold = str_replace('{{componentNamespace}}',$componentNamespace,$controllerScaffold);
        $controllerScaffold = str_replace('{{componentTitle}}',$componentTitle,$controllerScaffold);
        $controllerScaffold = str_replace('{{componentDescription}}',$componentDescription,$controllerScaffold);
        $controllerScaffold = str_replace('{{componentIcon}}',$componentIcon,$controllerScaffold);
        $controllerScaffold = str_replace('{{componentUrl}}',$componentUrl,$controllerScaffold);
        $this->filesystem->put($componentPath.'/Controllers/'.$componentNamespace.'Controller.php',$controllerScaffold);

        $viewScaffold = str_replace('{{componentNamespace}}',$componentNamespace,$viewScaffold);
        $viewScaffold = str_replace('{{componentTitle}}',$componentTitle,$viewScaffold);
        $viewScaffold = str_replace('{{componentDescription}}',$componentDescription,$viewScaffold);
        $viewScaffold = str_replace('{{componentIcon}}',$componentIcon,$viewScaffold);
        $viewScaffold = str_replace('{{componentUrl}}',$componentUrl,$viewScaffold);
        $this->filesystem->put($componentPath.'/Views/index.blade.php',$viewScaffold);

        $routesScaffold = str_replace('{{componentNamespace}}',$componentNamespace,$routesScaffold);
        $routesScaffold = str_replace('{{componentTitle}}',$componentTitle,$routesScaffold);
        $routesScaffold = str_replace('{{componentDescription}}',$componentDescription,$routesScaffold);
        $routesScaffold = str_replace('{{componentIcon}}',$componentIcon,$routesScaffold);
        $routesScaffold = str_replace('{{componentUrl}}',$componentUrl,$routesScaffold);
        $this->filesystem->put($componentPath.'/routes.php',$routesScaffold);

        $this->info(PHP_EOL.'Component successfully created!'.PHP_EOL);
    }

    /**
     * format any component name
     *
     * @param $componentTitle
     * @return mixed
     */
    protected function formatToComponentNamespace($componentTitle)
    {
        $componentTitle = str_replace(['/','-'],' ',$componentTitle);

        return str_replace(' ','',ucwords($componentTitle));
    }

    /**
     * format to component url
     *
     * @param $componentTitle
     * @return mixed
     */
    protected function formatToComponentUrl($componentTitle)
    {
        return str_replace(' ','-',strtolower($componentTitle));
    }
}