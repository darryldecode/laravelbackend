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

class DeleteDirectoryCommand extends Command implements SelfHandling {
    /**
     * @var null
     */
    private $paths;

    /**
     * @param null|string|array $paths
     */
    public function __construct($paths = null)
    {
        parent::__construct();
        $this->paths = $paths;
    }

    /**
     * @param Filesystem $filesystem
     * @return CommandResult
     */
    public function handle(Filesystem $filesystem)
    {
        // check if user has permission
        if( ! $this->user->hasAnyPermission(['media.delete']) )
        {
            return new CommandResult(false, "Not enough permission.", null, 403);
        }

        if( is_null($this->paths) )
        {
            return new CommandResult(false, "No file path given.", null, 400);
        }

        // begin delete
        if( is_array($this->paths) )
        {
            foreach($this->paths as $dir)
            {
                $filesystem->deleteDirectory($dir);
            }
        }
        else
        {
            $filesystem->deleteDirectory($this->paths);
        }

        // all good
        return new CommandResult(true, "directories successfully deleted.", null, 200);
    }
}