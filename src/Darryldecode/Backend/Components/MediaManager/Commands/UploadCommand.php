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
use Darryldecode\Backend\Components\MediaManager\Services\Image;
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
     * @param Image $image
     * @param Filesystem $filesystem
     * @return CommandResult
     */
    public function handle(Repository $config,Image $image,Filesystem $filesystem)
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
            // normalize file name
            $normalizedFileName = $this->normalizeFileName($file->getClientOriginalName());

            // save the file
            $file->move(
                $this->getCurrentFullPath($config,$path),
                $normalizedFileName
            );

            $filePath  = $this->getCurrentFullPath($config,$path).$normalizedFileName;
            $file_name = pathinfo($filePath, PATHINFO_FILENAME);
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);

            // produce thumbnail sizes
            $sizes = $config->get('backend.backend.thumb_sizes');

            if( getimagesize($filePath) )
            {
                foreach($sizes as $key => $dimension)
                {
                    $targetDir = $this->getCurrentFullPath($config,$path).$key.DIRECTORY_SEPARATOR;

                    if( ! $filesystem->exists($targetDir) )
                    {
                        $filesystem->makeDirectory($this->normalizePath($path).DIRECTORY_SEPARATOR.$key.DIRECTORY_SEPARATOR);
                    }

                    $image::createThumbnail(
                        $filePath,
                        $dimension[0],
                        $dimension[1],
                        $targetDir.$file_name.'.'.$extension
                    );
                }
            }
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

    /**
     * produce proper thumbname according to size
     * ex. from myFile.jpg -> myFile_small.jpg
     *
     * @param $file_name
     * @param $file_size_name
     * @param $file_extension
     * @return string
     */
    protected function produceThumbFileName($file_name, $file_size_name, $file_extension)
    {
        return $file_name.'_'.$file_size_name.'.'.$file_extension;
    }

    /**
     * @param Repository $config
     * @param string $path
     * @return string
     */
    protected function getCurrentFullPath($config, $path)
    {
        return $config->get('filesystems.disks.local.root').DIRECTORY_SEPARATOR.$this->normalizePath($path).DIRECTORY_SEPARATOR;
    }

    /**
     * when uploading a file, we will remove dashes because dashes are use in UI as size convention
     *
     * @param $string
     * @return mixed
     */
    protected function normalizeFileName($string)
    {
        return str_replace('_','',$string);
    }
}