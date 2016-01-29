<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/4/2015
 * Time: 7:47 PM
 */

namespace Darryldecode\Backend\Components\MediaManager\Commands;

use Darryldecode\Backend\Base\Commands\Command;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Config\Repository;

class ListCommand extends Command implements SelfHandling {
    /**
     * @var null
     */
    private $path;

    /**
     * @param null $path
     * @param bool $disablePermissionChecking
     */
    public function __construct($path = null, $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->path = $path;
        $this->disablePermissionChecking = $disablePermissionChecking;
    }

    /**
     * @param Filesystem $filesystem
     * @param Repository $config
     * @return CommandResult
     */
    public function handle(Filesystem $filesystem, Repository $config)
    {
        $path = (is_null($this->path)) ? DIRECTORY_SEPARATOR : $this->path;

        $response = [];
        $response['files']          = $filesystem->files($path);
        $response['directories']    = $filesystem->directories($path);
        $response['base_path']      = $config->get('filesystems.disks.local.root');
        $response['paths']          = $this->breakDownPath($path);
        $response['current_path']   = $this->path;
        $response['is_empty']       = $this->isResultIsEmpty($response['files'], $response['directories']);

        // all good
        return new CommandResult(true, "List command successful.", $response, 200);
    }

    /**
     * check if result is empty
     *
     * @param $files
     * @param $directories
     * @return bool
     */
    protected function isResultIsEmpty($files, $directories)
    {
        return (count($files)==0) && (count($directories)==0);
    }

    /**
     * breakdown given path to array
     *
     * @param $path
     * @return array
     */
    protected function breakDownPath($path)
    {
        if( $path == '/' ) return array('/');

        $paths = explode(DIRECTORY_SEPARATOR,trim($path,'/'));

        array_unshift($paths, '/');

        return $paths;
    }
}