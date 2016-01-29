<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/6/2015
 * Time: 12:45 PM
 */

namespace Darryldecode\Backend\Components\Dashboard;

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
            'name' => 'Dashboard',
            'description' => 'A dashboard',
        );
    }

    /**
     * returns the available permissions of the component
     *
     * @return array
     */
    public function getAvailablePermissions()
    {
        $availablePermissions = array();

        return $availablePermissions;
    }

    /**
     * the component navigation
     *
     * @return ComponentNavigationCollection
     */
    public function getNavigation()
    {
        $navs = new ComponentNavigationCollection();

        return $navs;
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
            'namespace' => 'dashboard',
        );
    }

    /**
     * get components routes path
     *
     * @return array
     */
    public function getRoutesControl()
    {
        return array(
            'dir' => __DIR__.'/routes.php',
            'namespace' => 'Darryldecode\\Backend\\Components\\Dashboard\\Controllers',
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