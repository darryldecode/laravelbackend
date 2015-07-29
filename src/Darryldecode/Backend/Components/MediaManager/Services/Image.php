<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 7/28/2015
 * Time: 11:29 PM
 */

namespace Darryldecode\Backend\Components\MediaManager\Services;

use Intervention\Image\ImageManager;

class Image {

    /**
     * creates a thumbnail
     *
     * @param $imagePath
     * @param null $width
     * @param null $height
     * @param $target
     * @return bool
     */
    public static function createThumbnail($imagePath, $width = null, $height = null, $target)
    {
        $manager = new ImageManager();

        $manager->make($imagePath)
            ->resize($width, $height)
            ->save($target);

        return true;
    }
}