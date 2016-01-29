<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/29/2015
 * Time: 4:17 PM
 */

namespace Darryldecode\Backend\Components\User\Commands;

use Darryldecode\Backend\Base\Commands\Command;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Darryldecode\Backend\Components\User\Models\User;
use Darryldecode\Backend\Components\User\Models\Group;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Validation\Factory;

class UpdateUserCommand extends Command implements SelfHandling {

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
    private $permissions;
    /**
     * array of group Id's the user needs to be associated with
     *
     * @var
     */
    private $groups;
    /**
     * @var
     */
    private $id;
    /**
     * @var null
     */
    private $password;

    /**
     * @param null $id
     * @param null $firstName
     * @param null $lastName
     * @param null $email
     * @param null $password
     * @param null $permissions
     * @param null $groups
     * @param bool $disablePermissionChecking
     */
    public function __construct($id = null,
                                $firstName = null,
                                $lastName = null,
                                $email = null,
                                $password = null,
                                $permissions = null,
                                $groups = null,
                                $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->password = $password;
        $this->permissions = $permissions;
        $this->groups = $groups;
        $this->id = $id;
        $this->args = get_defined_vars();
        $this->disablePermissionChecking = $disablePermissionChecking;
    }

    /**
     * @param User $user
     * @param Dispatcher $dispatcher
     * @param Group $group
     * @return CommandResult
     */
    public function handle(User $user, Dispatcher $dispatcher, Group $group)
    {
        // check user permission
        if( ! $this->disablePermissionChecking )
        {
            if( ! $this->user->hasAnyPermission(['user.manage']) )
            {
                return new CommandResult(false, CommandResult::$responseForbiddenMessage, null, 403);
            }
        }

        // fire creating
        $dispatcher->fire('user.updating', array($this->args));

        // find the user to be updated
        if( ! $userToBeUpdated = $user->find($this->id) )
        {
            return new CommandResult(false, "User not found.", null, 404);
        }

        // begin update
        $userToBeUpdated->first_name = $this->firstName ? $this->firstName : $userToBeUpdated->first_name;
        $userToBeUpdated->last_name = $this->lastName ? $this->lastName : $userToBeUpdated->last_name;
        $userToBeUpdated->permissions = $this->isPermissionsProvided($this->permissions) ? $this->transform($this->permissions) : $userToBeUpdated->permissions;

        if( $this->isEmailIsProvidedAndIsNew($userToBeUpdated->email, $this->email) )
        {
            if( $this->isEmailAlreadyInUsed($user, $this->email) )
            {
                return new CommandResult(false, "Email already in used.", null, 400);
            }
            else
            {
                $userToBeUpdated->email = $this->email;
            }
        }

        if( $this->isPasswordIsProvided() )
        {
            $userToBeUpdated->password = $this->password;
        }

        // save
        $userToBeUpdated->save();

        // add to group if there's any
        if( $this->isGroupIdsAreProvided($this->groups) )
        {
            // delete all groups first
            $userToBeUpdated->groups()->detach();

            // re attach
            foreach($this->groups as $groupId)
            {
                $g = $group->find($groupId);

                if( $g )
                {
                    $userToBeUpdated->groups()->attach($g);
                }
            }
        }

        // fire created user
        $dispatcher->fire('user.updated', array($userToBeUpdated));

        // return response
        return new CommandResult(true, "User successfully updated.", $userToBeUpdated, 200);
    }

    /**
     * check if password is provided
     *
     * @return bool
     */
    protected function isPasswordIsProvided()
    {
        return ((!is_null($this->password)) && (!empty($this->password)));
    }

    /**
     * check if email is provided and is a new email
     *
     * @param string $userEmail
     * @param string $providedEmail
     * @return bool
     */
    protected function isEmailIsProvidedAndIsNew($userEmail, $providedEmail)
    {
        return ( ! is_null($providedEmail) && ($providedEmail != $userEmail) );
    }

    /**
     * check if email is provided
     *
     * @param User $user
     * @param string $email
     * @return bool
     */
    protected function isEmailAlreadyInUsed(User $user, $email)
    {
        $found = $user->with(array())->where('email',$email)->first();

        return $found ? true : false;
    }

    /**
     * check if group Ids are provided
     *
     * @param array|null $groupIds
     * @return bool
     */
    protected function isGroupIdsAreProvided($groupIds)
    {
        return !is_null($groupIds);
    }

    /**
     * check if permissions is provided
     *
     * @param $permissions
     * @return bool
     */
    protected function isPermissionsProvided($permissions)
    {
        return !is_null($permissions);
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