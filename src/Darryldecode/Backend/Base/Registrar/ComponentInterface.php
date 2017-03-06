<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/6/2015
 * Time: 12:30 PM
 */

namespace Darryldecode\Backend\Base\Registrar;


interface ComponentInterface {

    /**
     * returns the component info
     *
     * @return array
     */
    public function getComponentInfo();

    /**
     * returns the available permissions of the component
     *
     * @return array
     */
    public function getAvailablePermissions();

    /**
     * the component navigation
     *
     * @return ComponentNavigationCollection
     */
    public function getNavigation();

    /**
     * returns the views path
     *
     * @return array
     */
    public function getViewsPath();

    /**
     * get components routes and controller namespace
     *
     * @return array
     */
    public function getRoutesControl();

    /**
     * get component scripts for header
     *
     * @return array
     */
    public function getHeaderScripts();

    /**
     * get component scripts for footer
     *
     * @return array
     */
    public function getFooterScripts();
}