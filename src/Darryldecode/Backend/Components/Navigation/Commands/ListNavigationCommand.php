<?php namespace Darryldecode\Backend\Components\Navigation\Commands;
/**
 * this Command is use to list the backend dashboard navigation
 *
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/5/2015
 * Time: 9:43 PM
 */

use Darryldecode\Backend\Base\Commands\Command;

use Darryldecode\Backend\Base\Commands\CommandResult;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Support\Collection;

class ListNavigationCommand extends Command implements SelfHandling {

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * handle list navigation logic
     *
     * @return CommandResult
     */
    public function handle()
    {
        $registeredNavigations = new Collection($this->app['backend']->getNavigations());

        $navs = $registeredNavigations->filter(function($nav)
        {
            if( $nav->isDropdown() )
            {
                $nav->subMenus = $nav->subMenus->filter(function ($subMenu)
                {
                    if( count($subMenu->getRequiredPermissions()) == 0 ) return true;

                    return $this->user->hasAnyPermission($subMenu->getRequiredPermissions());
                });
            }

            if( count($nav->getRequiredPermissions()) == 0 ) return true;

            return $this->user->hasAnyPermission($nav->getRequiredPermissions());
        });

        // all good
        return new CommandResult(true, "List navigation command successful.", $navs, 200);
    }
}