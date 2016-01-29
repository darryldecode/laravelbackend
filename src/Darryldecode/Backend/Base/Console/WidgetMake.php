<?php

namespace Darryldecode\Backend\Base\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class WidgetMake extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backend:widget-create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new backend custom dashboard widget.';

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
        $widgetTitle       = $this->ask('Enter widget name');
        $widgetDescription = $this->ask('Enter widget description');
        $widgetNamespace   = $this->formatToComponentNamespace($widgetTitle);

        $backendPath = app_path().'/Backend';
        $widgetPath  = $backendPath.'/Widgets/'.$widgetNamespace;

        if( ! $this->filesystem->isDirectory($backendPath) )
        {
            $this->filesystem->makeDirectory($backendPath);
        }

        if( ! $this->filesystem->isDirectory($backendPath.'/Widgets') )
        {
            $this->filesystem->makeDirectory($backendPath.'/Widgets');
        }

        if( $this->filesystem->isDirectory($widgetPath) )
        {
            $this->error('Widget already exist.');
            exit;
        }

        // create necessary component directories
        $this->filesystem->makeDirectory($widgetPath);

        // process scaffolds
        $widgetScaffold = $this->filesystem->get($scaffoldsPath.'Widget');
        $widgetViewScaffold = $this->filesystem->get($scaffoldsPath.'widget-view');

        $this->comment(PHP_EOL.'Creating widget...'.PHP_EOL);

        $widgetScaffold = str_replace('{{widgetNamespace}}',$widgetNamespace,$widgetScaffold);
        $widgetScaffold = str_replace('{{widgetTitle}}',$widgetTitle,$widgetScaffold);
        $widgetScaffold = str_replace('{{widgetDescription}}',$widgetDescription,$widgetScaffold);
        $this->filesystem->put($widgetPath.'/Widget.php',$widgetScaffold);

        $widgetViewScaffold = str_replace('{{widgetNamespace}}',$widgetNamespace,$widgetViewScaffold);
        $widgetViewScaffold = str_replace('{{widgetTitle}}',$widgetTitle,$widgetViewScaffold);
        $widgetViewScaffold = str_replace('{{widgetDescription}}',$widgetDescription,$widgetViewScaffold);
        $this->filesystem->put($widgetPath.'/widget-view.blade.php',$widgetViewScaffold);

        $this->info(PHP_EOL.'Widget successfully created!'.PHP_EOL);
    }

    /**
     * format any widget name
     *
     * @param $widgetTitle
     * @return mixed
     */
    protected function formatToComponentNamespace($widgetTitle)
    {
        $widgetTitle = str_replace(['/','-'],' ',$widgetTitle);

        return str_replace(' ','',ucwords($widgetTitle));
    }
}