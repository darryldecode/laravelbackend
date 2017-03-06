<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/6/2015
 * Time: 1:49 PM
 */

namespace Darryldecode\Backend\Components\MediaManager;

use Darryldecode\Backend\Base\Registrar\ComponentInterface;
use Darryldecode\Backend\Base\Registrar\ComponentNavigation;
use Darryldecode\Backend\Base\Registrar\ComponentNavigationCollection;

class Component implements ComponentInterface {

    /**
     * returns the component info
     *
     * @return array
     */
    public function getComponentInfo()
    {
        return array(
            'name' => 'Media Manager',
            'description' => 'A simple media manager to manage you files',
        );
    }

    /**
     * returns the available permissions of the component
     *
     * @return array
     */
    public function getAvailablePermissions()
    {
        $availablePermissions = array(
            array(
                'title' => 'Manage Media',
                'description' => 'Enable\'s the user to manage media. This includes access, uploads and modification capabilities',
                'permission' => 'media.manage',
            ),
            array(
                'title' => 'Delete A Media',
                'description' => 'Gives the user authorization to perform delete action in Media Manager, this includes deleting a folder/file.',
                'permission' => 'media.delete',
            ),
        );

        return $availablePermissions;
    }

    /**
     * the component navigation
     *
     * @return ComponentNavigationCollection
     */
    public function getNavigation()
    {
        $mediaManager = new ComponentNavigation('Media Manager','fa fa-files-o',url(config('backend.backend.base_url').'/media_manager'));
        $mediaManager->setRequiredPermissions(['media.manage']);

        $navCollection = new ComponentNavigationCollection();
        $navCollection->push($mediaManager);

        return $navCollection;
    }

    /**
     * returns the views path
     *
     * @return array
     */
    public function getViewsPath()
    {
        return array(
            'dir' => __DIR__.'/Views',
            'namespace' => 'mediaManager',
        );
    }

    /**
     * get components routes and controller namespace
     *
     * @return array
     */
    public function getRoutesControl()
    {
        return array(
            'dir' => __DIR__.'/routes.php',
            'namespace' => 'Darryldecode\\Backend\\Components\\MediaManager\\Controllers',
        );
    }

    /**
     * get component scripts for header
     *
     * @return array
     */
    public function getHeaderScripts()
    {
        return array();
    }

    /**
     * get component scripts for footer
     *
     * @return array
     */
    public function getFooterScripts()
    {
        return array();
    }
}

return new Component;