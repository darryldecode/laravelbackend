<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/5/2015
 * Time: 9:46 PM
 */

namespace Darryldecode\Backend\Base\Registrar;


use Illuminate\Support\Collection;

class ComponentNavigation {
    /**
     * the label
     *
     * @var
     */
    public $label;
    /**
     * the icon
     *
     * @var
     */
    public $icon;
    /**
     * the link/url of the navigation
     *
     * @var string
     */
    public $link;
    /**
     * the required permissions
     *
     * @var array
     */
    public $requiredPermissions = array();
    /**
     * the sub menus if the navigation is a dropdown
     *
     * @var \Illuminate\Support\Collection
     */
    public $subMenus = array();
    /**
     * @var bool
     */
    public $dropdown;

    /**
     * @param $label
     * @param $icon
     * @param $link
     * @param bool $dropdown
     */
    public function __construct($label, $icon, $link, $dropdown = false)
    {
        $this->label = $label;
        $this->icon = $icon;
        $this->link = $link;
        $this->dropdown = $dropdown;
        $this->subMenus = new Collection();
    }

    /**
     * sets the required permissions of the navigation
     *
     * @param array $permissions
     */
    public function setRequiredPermissions($permissions = array())
    {
        $this->requiredPermissions = $permissions;
    }

    /**
     * @param \Darryldecode\Backend\Base\Registrar\ComponentNavigation $nav
     */
    public function addSubMenu($nav)
    {
        $this->subMenus->push($nav);
    }

    /**
     * determine if the menu is a drop down
     *
     * @return bool
     */
    public function isDropdown()
    {
        return $this->dropdown;
    }

    /**
     * return sub menus
     *
     * @return Collection
     */
    public function getSubMenus()
    {
        return $this->subMenus;
    }

    /**
     * the label of the menu
     *
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * the link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * the icon of the menu
     *
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * the required permissions of the menu
     *
     * @return array
     */
    public function getRequiredPermissions()
    {
        return $this->requiredPermissions;
    }
}