<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/5/2015
 * Time: 5:17 PM
 */

namespace Darryldecode\Backend\Components\MediaManager\Commands;

use Darryldecode\Backend\Base\Commands\Command;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Config\Repository;

class DeleteFileCommand extends Command implements SelfHandling {
    /**
     * @var null
     */
    private $paths;

    /**
     * @param null|string|array $paths
     * @param bool $disablePermissionChecking
     */
    public function __construct($paths = null, $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->paths = $paths;
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
            if( ! $this->user->hasAnyPermission(['media.delete']) )
            {
                return new CommandResult(false, "Not enough permission.", null, 403);
            }
        }

        if( is_null($this->paths) )
        {
            return new CommandResult(false, "No file path given.", null, 400);
        }

        // begin delete
        if( ! $filesystem->delete($this->paths) )
        {
            return new CommandResult(false, "Failed to delete file.", null, 400);
        }

        // all good
        return new CommandResult(true, "File(s) successfully deleted.", null, 200);
    }
}