<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/29/2015
 * Time: 8:41 AM
 */

namespace Darryldecode\Backend\Components\User\Commands;

use Darryldecode\Backend\Base\Commands\Command;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Utility\Helpers;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Validation\Factory as Validator;

class UpdateGroupCommand extends Command implements SelfHandling {

    /**
     * @var
     */
    private $name;
    /**
     * @var
     */
    private $permissions;
    /**
     * @var
     */
    private $id;

    /**
     * @param $id
     * @param string $name
     * @param array $permissions
     * @param bool $disablePermissionChecking
     */
    public function __construct($id = null, $name = null, $permissions = array(), $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->name = $name;
        $this->permissions = $permissions;
        $this->args = get_defined_vars();
        $this->id = $id;
        $this->disablePermissionChecking = $disablePermissionChecking;
    }

    /**
     * handle group command update logic
     *
     * @param Group $group
     * @param Dispatcher $dispatcher
     * @param Validator $validator
     * @return CommandResult
     */
    public function handle(Group $group, Dispatcher $dispatcher, Validator $validator)
    {
        // check user permission
        if( ! $this->disablePermissionChecking )
        {
            if( ! $this->user->hasAnyPermission(['user.manage']) )
            {
                return new CommandResult(false, CommandResult::$responseForbiddenMessage, null, 403);
            }
        }

        // fire updating
        $dispatcher->fire('group.updating', array($this->args));

        // try to find group to be updated
        if( ! $groupToBeUpdated = $group->find($this->id) )
        {
            return new CommandResult(false, "Group not found.", null, 400);
        }

        // begin update
        $groupToBeUpdated->name = $this->name ? $this->name : $groupToBeUpdated->name;
        $groupToBeUpdated->permissions = $this->permissions ? $this->transform($this->permissions) : $groupToBeUpdated->permissions;
        $groupToBeUpdated->save();

        // fire creating
        $dispatcher->fire('group.updated', array($groupToBeUpdated));

        // all good
        return new CommandResult(true, "Group successfully updated.", $groupToBeUpdated, 200);
    }

    /**
     * transform multi dimensional format key value pair of permissions to associative
     *
     * @param $permissions
     * @return array
     */
    protected function transform($permissions)
    {
        if( is_array(head($permissions)) )
        {
            $ar = [];

            foreach($permissions as $k => $v)
            {
                $ar[$v['key']] = $v['value'];
            }

            return $ar;
        }
        else
        {
            return $permissions;
        }
    }
}