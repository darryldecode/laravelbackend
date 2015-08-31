<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/6/2015
 * Time: 12:42 PM
 */

namespace Darryldecode\Backend\Base\Registrar;

class Registrar {

    /**
     * the laravel backend version
     */
    const VERSION = '1.0.19';
    const VERSION_NAME = 'Alpha';

    /**
     * backend active components
     *
     * @var array
     */
    protected $activeComponents = array();

    /**
     * backend active widgets
     *
     * @var array
     */
    protected $activeWidgets = array();

    /**
     * @var array
     */
    protected $navigation = array();

    /**
     * the views
     *
     * @var array
     */
    protected $views = array();

    /**
     * the available permissions from components
     *
     * @var array
     */
    protected $availablePermissions = array();

    /**
     * the available routes from components
     *
     * @var array
     */
    protected $routes = array();

    /**
     * the header scripts
     *
     * @var array
     */
    protected $headerScripts = array();

    /**
     * the footer scripts
     *
     * @var array
     */
    protected $footerScripts = array();

    public function __construct()
    {
        //
    }

    /**
     * adds component
     *
     * @param \Darryldecode\Backend\Base\Registrar\ComponentInterface|array $component
     * @return $this
     */
    public function addComponent($component)
    {
        if(is_array($component))
        {
            foreach($component as $c)
            {
                $this->addComponent($c);
            }
        }
        else
        {
            array_push($this->activeComponents, $component);
        }

        return $this;
    }

    /**
     * add widget to registrar
     *
     * @param \Darryldecode\Backend\Base\Registrar\WidgetInterface|array $widget
     * @return $this
     */
    public function addWidget($widget)
    {
        if(is_array($widget))
        {
            foreach($widget as $w)
            {
                $this->addWidget($w);
            }
        }
        else
        {
            array_push($this->activeWidgets, $widget);
            if(count($widget->getHeaderScripts()) > 0) array_push($this->headerScripts, $widget->getHeaderScripts());
            if(count($widget->getFooterScripts()) > 0) array_push($this->footerScripts, $widget->getFooterScripts());
        }

        return $this;
    }

    /**
     * init navigations
     */
    public function initNavigation()
    {
        foreach($this->activeComponents as $component)
        {
            $component->getNavigation()->each(function($nav)
            {
                array_push($this->navigation, $nav);
            });
        }
    }

    /**
     * init permissions
     */
    public function initPermissions()
    {
        foreach($this->activeComponents as $component)
        {
            foreach($component->getAvailablePermissions() as $permission)
            {
                array_push($this->availablePermissions, $permission);
            }
        }
    }

    /**
     * init routes
     */
    public function initRoutes()
    {
        foreach($this->activeComponents as $component)
        {
            array_push($this->routes, $component->getRoutesControl());
        }
    }

    /**
     * init views
     */
    public function initViews()
    {
        foreach($this->activeComponents as $component)
        {
            array_push($this->views, $component->getViewsPath());
        }
    }

    /**
     * init added header scripts
     */
    public function initAddHeaderScripts()
    {
        foreach($this->activeComponents as $component)
        {
            if(count($component->getHeaderScripts()) > 0) array_push($this->headerScripts, $component->getHeaderScripts());
        }
    }

    /**
     * init added footer scripts
     */
    public function initAddFooterScripts()
    {
        foreach($this->activeComponents as $component)
        {
            if(count($component->getFooterScripts()) > 0) array_push($this->footerScripts, $component->getFooterScripts());
        }
    }

    /**
     * get navigations
     *
     * @return array
     */
    public function getNavigations()
    {
        return $this->navigation;
    }

    /**
     * get view paths
     *
     * @return array
     */
    public function getViewsPaths()
    {
        return $this->views;
    }

    /**
     * the combined permissions across registered components
     *
     * @return array
     */
    public function getAvailablePermissions()
    {
        return $this->availablePermissions;
    }

    /**
     * get routes dirs
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * get all registered active components
     *
     * @return array
     */
    public function getActiveComponents()
    {
        return $this->activeComponents;
    }

    /**
     * returns all the registered active widgets
     *
     * @return array
     */
    public function getActiveWidgets()
    {
        return $this->activeWidgets;
    }

    /**
     * returns the current version
     *
     * @return array
     */
    public function getVersion()
    {
        return array(
            'version' => self::VERSION,
            'name' => self::VERSION_NAME,
        );
    }

    /**
     * get added header scripts by all active components
     *
     * @return array
     */
    public function getAddedHeaderScripts()
    {
        return $this->headerScripts;
    }

    /**
     * get added footer scripts by all active components
     *
     * @return array
     */
    public function getAddedFooterScripts()
    {
        return $this->footerScripts;
    }
}