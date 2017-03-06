<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/6/2015
 * Time: 12:45 PM
 */

namespace Darryldecode\Backend\Components\Navigation;


use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
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
            'name' => 'Navigation Builder',
            'description' => 'A simple navigation builder.',
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
                'title' => 'Manage Navigation Builder',
                'description' => 'Enable\'s the user to manage Navigation Builder. This includes access and modification capabilities',
                'permission' => 'navigationBuilder.manage',
            ),
            array(
                'title' => 'Delete Custom Navigation Entry',
                'description' => 'Gives the user authorization to perform delete action in Navigation Builder.',
                'permission' => 'navigationBuilder.delete',
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
        // the content builder navigation
        $customNavigationBuilder = new ComponentNavigation('Navigation Builder','fa fa-align-justify',url(config('backend.backend.base_url').'/navigation/builder'));
        $customNavigationBuilder->setRequiredPermissions(['navigationBuilder.manage']);

        $navs = new ComponentNavigationCollection();
        $navs->push($customNavigationBuilder);

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
            'namespace' => 'navigationBuilder',
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
            'namespace' => 'Darryldecode\\Backend\\Components\\Navigation\\Controllers',
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