<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/5/2015
 * Time: 2:30 PM
 */

namespace Darryldecode\Backend\Components\MediaManager\Commands;

use Darryldecode\Backend\Base\Commands\Command;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Config\Repository;

class UploadCommand extends Command implements SelfHandling {
    /**
     * @var null
     */
    private $files;
    /**
     * @var null
     */
    private $path;

    /**
     * @param null $files
     * @param null $path
     * @param bool $disablePermissionChecking
     */
    public function __construct($files = null, $path = null, $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->files = $files;
        $this->path = $path;
        $this->disablePermissionChecking = $disablePermissionChecking;
    }

    /**
     * @param Repository $config
     * @return CommandResult
     */
    public function handle(Repository $config)
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

        // upload files
        foreach($this->files as $file)
        {
            $file->move(
                $config->get('filesystems.disks.local.root').'/'.$this->normalizePath($path),
                $file->getClientOriginalName()
            );
        }

        // all good
        return new CommandResult(true, "File(s) successfully uploaded.", null, 200);
    }

    /**
     * nomalizes path slashes
     *
     * @param $path
     * @return string
     */
    protected function normalizePath($path)
    {
        if( $path == '/' ) return '/';

        return ltrim($path, '/');
    }
}