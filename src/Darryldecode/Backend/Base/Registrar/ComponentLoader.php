<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/11/2015
 * Time: 7:19 PM
 */

namespace Darryldecode\Backend\Base\Registrar;

use Illuminate\Filesystem\Filesystem;

class ComponentLoader {

    /**
     * @var
     */
    protected $path;
    /**
     * @var Filesystem
     */
    protected $filesystem;
    /**
     * the Component implementation
     *
     * @var string
     */
    protected $componentInterface = 'Darryldecode\Backend\Base\Registrar\ComponentInterface';

    /**
     * List of options
     *
     * @var array
     */
    protected $options = [];

    /**
     * @param $path
     * @param Filesystem $filesystem
     * @param array $options [disabled_components => ['Component Name']]
     */
    public function __construct($path, Filesystem $filesystem, array $options = [])
    {
        $this->path = $path;
        $this->filesystem = $filesystem;
        $this->options = $options;
    }

    /**
     * get available component instances
     *
     * @return array
     */
    public function getAvailableComponentInstances()
    {
        return $this->extractComponentInstances();
    }

    /**
     * extracts the component instances
     *
     * @return array
     */
    protected function extractComponentInstances()
    {
        $componentInstances = [];

        // let's make sure that components path given is a directory
        if( $this->filesystem->isDirectory($this->path) )
        {
            foreach($this->filesystem->directories($this->path) as $dir)
            {
                if( $this->filesystem->exists($dir.'/Component.php') )
                {
                    $componentInstance = require_once $dir.'/Component.php';

                    if( $componentInstance instanceof ComponentInterface )
                    {
                        if(!in_array($componentInstance->getComponentInfo()['name'],$this->options['disabled_components']))
                        {
                            array_push(
                                $componentInstances,
                                $componentInstance
                            );
                        }
                    }
                }
            }
        }

        return $componentInstances;
    }
}