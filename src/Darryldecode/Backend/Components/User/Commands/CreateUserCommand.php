<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/25/2015
 * Time: 9:57 PM
 */

namespace Darryldecode\Backend\Components\User\Commands;

use Darryldecode\Backend\Base\Commands\Command;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Darryldecode\Backend\Components\User\Models\User;
use Darryldecode\Backend\Components\User\Models\Group;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Config\Repository;

class CreateUserCommand extends Command implements SelfHandling {
    /**
     * @var
     */
    private $firstName;
    /**
     * @var
     */
    private $lastName;
    /**
     * @var
     */
    private $email;
    /**
     * @var
     */
    private $password;
    /**
     * @var
     */
    private $permissions;
    /**
     * array of group Id's the user needs to be associated with
     *
     * @var
     */
    private $groups;

    /**
     * @param $firstName
     * @param $lastName
     * @param $email
     * @param $password
     * @param array $permissions
     * @param array $groups
     * @param bool $disablePermissionChecking
     */
    public function __construct($firstName, $lastName, $email, $password, $permissions = array(), $groups = array(), $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->password = $password;
        $this->permissions = $permissions;
        $this->groups = $groups;
        $this->disablePermissionChecking = $disablePermissionChecking;
    }

    /**
     * @param User $user
     * @param Factory $validator
     * @param Dispatcher $dispatcher
     * @param Group $group
     * @param Repository $config
     * @return CommandResult
     */
    public function handle(User $user, Factory $validator, Dispatcher $dispatcher, Group $group, Repository $config)
    {
        // check user permission
        if( ! $this->disablePermissionChecking )
        {
            if( ! $this->user->hasAnyPermission(['user.manage']) )
            {
                return new CommandResult(false, CommandResult::$responseForbiddenMessage, null, 403);
            }
        }

        // prepare the user model
        $user = $this->createUserModel($user, $config);

        // validate data
        $validationResult = $validator->make(array(
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'password' => $this->password,
        ), $user->getValidationRules());

        if( $validationResult->fails() )
        {
            return new CommandResult(false, $validationResult->getMessageBag()->first(), null, 400);
        }

        // prepare data to be store
        $userDataToBeCreated = array(
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'password' => $this->password,
            'permissions' => $this->transform($this->permissions)
        );

        // fire creating
        $dispatcher->fire('user.creating', array($userDataToBeCreated));

        $createdUser = $user->create($userDataToBeCreated);

        if( ! $createdUser ) return new CommandResult(false, "Failed to create user.", null, 400);

        // add to group if there's any
        if( count($this->groups) > 0 )
        {
            foreach($this->groups as $groupId)
            {
                $g = $group->find($groupId);

                if( $g )
                {
                    $createdUser->groups()->attach($g);
                }
            }
        }

        // fire created user
        $dispatcher->fire('user.created', array($createdUser));

        // return response
        return new CommandResult(true, "User successfully created.", $createdUser, 201);
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

    /**
     * @param $user \Darryldecode\Backend\Components\User\Models\User
     * @param $config \Illuminate\Config\Repository
     * @return mixed
     */
    protected function createUserModel($user, $config)
    {
        if( ! $userModelUsed = $config->get('backend.backend.user_model') )
        {
            return $user;
        }

        $userModelUsed = new $userModelUsed();

        if( $userModelUsed instanceof User )
        {
            return $userModelUsed;
        }

        return $user;
    }
}