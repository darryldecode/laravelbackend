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

class MakeDirectoryCommand extends Command implements SelfHandling {
    /**
     * @var null
     */
    private $path;
    /**
     * @var null
     */
    private $dirName;

    /**
     * @param null $path
     * @param null $dirName
     * @param bool $disablePermissionChecking
     */
    public function __construct($path = null, $dirName = null, $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->path = $path;
        $this->dirName = $dirName;
        $this->disablePermissionChecking = $disablePermissionChecking;
    }

    /**
     * @param Filesystem $filesystem
     * @return CommandResult
     */
    public function handle(Filesystem $filesystem)
    {
        // check if user has permission
        if( ! $this->disablePermissionChecking )
        {
            if( ! $this->user->hasAnyPermission(['media.manage']) )
            {
                return new CommandResult(false, "Not enough permission.", null, 403);
            }
        }

        $path = (is_null($this->path)) ? '/' : $this->path;

        if( ! $dir = $filesystem->makeDirectory($this->normalizePath($path).$this->dirName) )
        {
            return new CommandResult(false, "Failed to create directory.", null, 400);
        };

        // all good
        return new CommandResult(true, "Make directory command successful.", array($dir), 201);
    }

    /**
     * just normalize path
     *
     * @param $path
     * @return string
     */
    protected function normalizePath($path)
    {
        return rtrim($path, '/').'/';
    }
}