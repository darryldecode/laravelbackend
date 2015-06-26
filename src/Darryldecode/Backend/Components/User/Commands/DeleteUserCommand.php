<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/30/2015
 * Time: 8:55 AM
 */

namespace Darryldecode\Backend\Components\User\Commands;

use Darryldecode\Backend\Base\Commands\Command;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Darryldecode\Backend\Components\User\Models\User;
use Darryldecode\Backend\Components\User\Models\Group;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;

class DeleteUserCommand extends Command implements SelfHandling {
    /**
     * the user Id
     *
     * @var
     */
    private $id;

    /**
     * @param null $id
     */
    public function __construct($id = null)
    {
        parent::__construct();
        $this->id = $id;
        $this->args = get_defined_vars();
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
        if( ! $this->user->hasAnyPermission(['user.delete']) )
        {
            return new CommandResult(false, CommandResult::$responseForbiddenMessage, null, 403);
        }

        // find the user
        if( ! $userToBeDelete = $user->find($this->id) )
        {
            return new CommandResult(false, "User not found.", null, 404);
        }

        // fire deleting
        $dispatcher->fire('user.deleting', array($this->args));

        // begin deletion
        $userToBeDelete->groups()->detach();
        $userToBeDelete->delete();

        // fire deleted
        $dispatcher->fire('user.deleted', array($userToBeDelete));

        // all good
        return new CommandResult(true, "User successfully deleted.", null, 200);
    }
}