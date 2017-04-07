<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/6/2015
 * Time: 1:49 PM
 */

namespace Darryldecode\Backend\Components\Media;

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
            'name' => 'Media',
            'description' => 'A simple media component to manage your files',
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
            )
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
        $mediaManager = new ComponentNavigation('Media','fa fa-files-o',url(config('backend.backend.base_url').'/media'));
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
            'namespace' => 'media',
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
            'namespace' => 'Darryldecode\\Backend\\Components\\Media\\Controllers',
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