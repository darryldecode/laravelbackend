<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/5/2015
 * Time: 7:00 PM
 */

namespace Darryldecode\Backend\Components\MediaManager\Commands;

use Darryldecode\Backend\Base\Commands\Command;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Config\Repository;

class MoveCommand extends Command implements SelfHandling {
    /**
     * @var null
     */
    private $path;
    /**
     * @var null
     */
    private $newPath;

    /**
     * @param null $path
     * @param null $newPath
     * @param bool $disablePermissionChecking
     */
    public function __construct($path = null, $newPath = null, $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->path = $path;
        $this->newPath = $newPath;
        $this->disablePermissionChecking = $disablePermissionChecking;
    }

    /**
     * @param Filesystem $filesystem
     * @param Repository $config
     * @return CommandResult
     */
    public function handle(Filesystem $filesystem, Repository $config)
    {
        // check if user has permission
        if( ! $this->disablePermissionChecking )
        {
            if( ! $this->user->hasAnyPermission(['media.manage']) )
            {
                return new CommandResult(false, "Not enough permission.", null, 403);
            }
        }

        $storage = rtrim($config->get('filesystems.disks.local.root'),'/').'/';

        $ext = pathinfo($storage.$this->path, PATHINFO_EXTENSION);

        if( empty($ext) )
        {
            rename($storage.$this->path, $storage.$this->newPath);
        }
        else
        {
            rename($storage.$this->path, $storage.$this->newPath.'.'.$ext);
        }

        // all good
        return new CommandResult(true, "File successfully renamed.", null, 200);
    }
}