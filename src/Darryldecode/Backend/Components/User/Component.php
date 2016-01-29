<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/6/2015
 * Time: 1:52 PM
 */

namespace Darryldecode\Backend\Components\User;

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
            'name' => 'User Manager',
            'description' => 'A flexible user management component.',
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
                'title' => 'Manage Users',
                'description' => 'Enable\'s the user to manage users. This includes access and modification capabilities',
                'permission' => 'user.manage',
            ),
            array(
                'title' => 'Delete A User',
                'description' => 'Gives the user authorization to perform delete action in User Manager, this includes deleting a user.',
                'permission' => 'user.delete',
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
        $users = new ComponentNavigation('Users','fa fa-users',url(config('backend.backend.base_url').'/users'));
        $users->setRequiredPermissions(['user.manage']);

        $navCollection = new ComponentNavigationCollection();
        $navCollection->push($users);

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
            'namespace' => 'userManager',
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
            'namespace' => 'Darryldecode\\Backend\\Components\\User\\Controllers',
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