<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/26/2015
 * Time: 3:27 PM
 */

namespace Darryldecode\Backend\Components\User\Commands;

use Darryldecode\Backend\Base\Commands\Command;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Validation\Factory as Validator;

class CreateGroupCommand extends Command implements SelfHandling {
    /**
     * @var
     */
    private $name;
    /**
     * @var
     */
    private $permissions;

    /**
     * @param string $name
     * @param array $permissions
     * @param bool $disablePermissionChecking
     */
    public function __construct($name = '', $permissions = array(), $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->name = $name;
        $this->permissions = $permissions;
        $this->disablePermissionChecking = $disablePermissionChecking;
    }

    /**
     * handle group creation logic
     *
     * @param Validator $validator
     * @param Dispatcher $dispatcher
     * @param Group $group
     * @return CommandResult
     */
    public function handle(Validator $validator, Dispatcher $dispatcher, Group $group)
    {
        // check user permission
        if( ! $this->disablePermissionChecking )
        {
            if( ! $this->user->hasAnyPermission(['user.manage']) )
            {
                return new CommandResult(false, CommandResult::$responseForbiddenMessage, null, 403);
            }
        }

        // validate data
        $validationResult = $validator->make(array(
            'name' => $this->name,
            'permissions' => $this->permissions,
        ), Group::$rules);

        if( $validationResult->fails() )
        {
            return new CommandResult(false, $validationResult->getMessageBag()->first(), null, 400);
        }

        // prepare data to be store
        $groupToBeCreated = array(
            'name' => $this->name,
            'permissions' => $this->transform($this->permissions),
        );

        // fire creating
        $dispatcher->fire('group.creating', array($groupToBeCreated));

        // create
        $createdGroup = $group->create($groupToBeCreated);

        if( ! $createdGroup ) return new CommandResult(false, "Failed to create user.", null, 400);

        // fire created user
        $dispatcher->fire('group.created', array($createdGroup));

        // return response
        return new CommandResult(true, "Group successfully created.", $createdGroup, 201);
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