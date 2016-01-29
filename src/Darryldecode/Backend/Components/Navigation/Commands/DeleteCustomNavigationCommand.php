<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 6/24/2015
 * Time: 11:04 PM
 */

namespace Darryldecode\Backend\Components\Navigation\Commands;

use Darryldecode\Backend\Base\Commands\Command;

use Darryldecode\Backend\Base\Commands\CommandResult;
use Darryldecode\Backend\Components\Navigation\Models\Navigation;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Validation\Factory;

class DeleteCustomNavigationCommand extends Command implements SelfHandling {

    /**
     * the ID of the navigation
     *
     * @var int
     */
    private $id;

    /**
     * @param null $id
     * @param bool $disablePermissionChecking
     */
    public function __construct($id = null, $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->id = $id;
        $this->args = func_get_args();
        $this->disablePermissionChecking = $disablePermissionChecking;
    }

    /**
     * @param Navigation $navigation
     * @param Factory $validator
     * @param Dispatcher $dispatcher
     * @return CommandResult
     */
    public function handle(Navigation $navigation, Factory $validator, Dispatcher $dispatcher)
    {
        // check if user has permission
        if( ! $this->disablePermissionChecking )
        {
            if( ! $this->user->hasAnyPermission(['navigationBuilder.delete']) )
            {
                return new CommandResult(false, "Not enough permission.", null, 403);
            }
        }

        // make sure we have a navigation to delete
        if( ! $nav = $navigation->find($this->id) )
        {
            return new CommandResult(false, "Navigation does not exist.", null, 400);
        }

        // fire before delete event
        $dispatcher->fire('navigationBuilder.deleting', array($nav, $this->args));

        // delete
        $nav->delete();

        // fire after create
        $dispatcher->fire('navigationBuilder.deleted', array($nav, $this->args));

        // all good
        return new CommandResult(true, "Navigation successfully deleted.", null, 200);
    }
}