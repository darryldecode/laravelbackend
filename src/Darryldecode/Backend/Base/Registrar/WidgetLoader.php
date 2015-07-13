<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 7/8/2015
 * Time: 10:03 PM
 */

namespace Darryldecode\Backend\Base\Registrar;

use Illuminate\Filesystem\Filesystem;

class WidgetLoader {

    /**
     * @var
     */
    protected $path;
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param $path
     * @param Filesystem $filesystem
     */
    public function __construct($path, Filesystem $filesystem)
    {
        $this->path = $path;
        $this->filesystem = $filesystem;
    }

    /**
     * get the available widget instances
     *
     * @return array
     */
    public function getAvailableWidgetInstances()
    {
        return $this->extractWidgetInstances();
    }

    /**
     * extract all the widget instances
     *
     * @return array
     */
    public function extractWidgetInstances()
    {
        $widgetInstances = [];

        // let's make sure that components path given is a directory
        if( $this->filesystem->isDirectory($this->path) )
        {
            foreach($this->filesystem->directories($this->path) as $dir)
            {
                if( $this->filesystem->exists($dir.'/Widget.php') )
                {
                    $widgetInstance = require_once $dir.'/Widget.php';

                    if( $widgetInstance instanceof WidgetInterface )
                    {
                        $disabledWidgetsArray = \Config::get('backend.backend.disabled_widgets');

                        if( is_null($disabledWidgetsArray) ) continue;

                        if( $widgetInstance->isWidgetActive() && (!in_array($widgetInstance->getWidgetInfo()['name'],$disabledWidgetsArray)) )
                        {
                            array_push(
                                $widgetInstances,
                                $widgetInstance
                            );
                        }
                    }
                }
            }
        }

        return $widgetInstances;
    }
}