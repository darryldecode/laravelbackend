<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/30/2015
 * Time: 4:38 PM
 */

namespace Darryldecode\Backend\Components\User\Commands;

use Darryldecode\Backend\Base\Commands\Command;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Darryldecode\Backend\Components\User\Models\User;
use Darryldecode\Backend\Components\User\Models\Group;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;

class DeleteGroupCommand extends Command implements SelfHandling {

    /**
     * the group Id
     *
     * @var
     */
    private $id;

    /**
     * @param $id
     * @param bool $disablePermissionChecking
     */
    public function __construct($id = null, $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->id = $id;
        $this->args = get_defined_vars();
        $this->disablePermissionChecking = $disablePermissionChecking;
    }

    /**
     * handle user deletion logic
     *
     * @param User $user
     * @param Group $group
     * @param Dispatcher $dispatcher
     * @return CommandResult
     */
    public function handle(User $user, Group $group, Dispatcher $dispatcher)
    {
        // check user permission
        if( ! $this->disablePermissionChecking )
        {
            if( ! $this->user->hasAnyPermission(['user.delete']) )
            {
                return new CommandResult(false, CommandResult::$responseForbiddenMessage, null, 403);
            }
        }

        // find the group
        if( ! $groupToBeDelete = $group->with('users')->find($this->id) )
        {
            return new CommandResult(false, "Group not found.", null, 404);
        }

        // fire deleting
        $dispatcher->fire('group.deleting', array($this->args));

        // begin deletion
        $groupToBeDelete->users()->detach();
        $groupToBeDelete->delete();

        // fire deleted
        $dispatcher->fire('group.deleted', array($groupToBeDelete));

        // all good
        return new CommandResult(true, "Group successfully deleted.", null, 200);
    }
}