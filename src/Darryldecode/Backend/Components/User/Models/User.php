<?php namespace Darryldecode\Backend\Components\User\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword;

	const PERMISSION_ALLOW 	    = 1;
	const PERMISSION_INHERIT    = 0;
	const PERMISSION_DENY 	    = -1;

	/**
	 * The valid permission values
	 * 1 means allow and 0 means deny
	 *
	 * @var array
	 */
	static $validPermissionValues = array(0, 1, -1);

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['first_name', 'last_name', 'email', 'password', 'permissions'];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];

	/**
	 * the rules of the User for validation before persisting
	 *
	 * @var array
	 */
	public static $rules = array(
		'first_name' => 'required',
		'last_name' => 'required',
		'email' => 'required|email|unique:users',
		'password' => 'required|min:8',
	);

	/**
	 * serializes permission attribute on the fly before saving to database
	 *
	 * @param $permissions
	 */
	public function setPermissionsAttribute($permissions)
	{
		$this->attributes['permissions'] = serialize($permissions);
	}

	/**
	 * unserializes permissions attribute before spitting out from database
	 *
	 * @return mixed
	 */
	public function getPermissionsAttribute()
	{
		return unserialize($this->attributes['permissions']);
	}

	/**
	 * returns the groups of the user
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function groups()
	{
		return $this->belongsToMany('Darryldecode\Backend\Components\User\Models\Group','user_group_pivot_table','user_id');
	}

	/**
	 * hash the password before any action like storing
	 *
	 * @param $password
	 */
	public function setPasswordAttribute($password)
	{
		$this->attributes['password'] = \Hash::make($password);
	}

	/**
	 * check if the user is superuser
	 *
	 * @return bool
	 */
	public function isSuperUser()
	{
		return $this->hasPermission('superuser');
	}

	/**
	 * check if user has permission
	 *
	 * @param string $permission
	 * @return bool
	 */
	public function hasPermission($permission)
	{
		$superUser = array_get($this->getCombinedPermissions(), 'superuser');

		if( $superUser === User::PERMISSION_ALLOW ) return true;

		foreach($this->getCombinedPermissions() as $p => $v)
		{
			if( $p == $permission )
			{
				return $v == User::PERMISSION_ALLOW;
			}
		}

		return false;
	}

	/**
	 * check if has any permissions
	 *
	 * @param array $permissions
	 * @return bool
	 */
	public function hasAnyPermission(array $permissions)
	{
		if( $this->isSuperUser() ) return true;

		$hasPermission = false;

		foreach($permissions as $permission)
		{
			if( $this->hasPermission($permission) )
			{
				$hasPermission = true;
			}
		}

		return $hasPermission;
	}

	/**
	 * check if user is in a group
	 *
	 * @param $group
	 * @return bool
	 */
	public function inGroup($group)
	{
		$found = false;

		if( is_string($group) )
		{
			$this->groups->each(function($g) use ($group, &$found)
			{
				if( $g->name == $group )
				{
					$found = true;
				}
			});

			return $found;
		}
        else if ( is_int($group) )
        {
            $this->groups->each(function($g) use ($group, &$found)
            {
                if( $g->id == $group )
                {
                    $found = true;
                }
            });

            return $found;
        }
		else if ( is_object($group) )
		{
			$this->groups->each(function($g) use ($group, &$found)
			{
				if( $g->name == $group->name )
				{
					$found = true;
				}
			});

			return $found;
		}
		else
		{
			return $found;
		}
	}

	/**
	 * the over all permissions of the user
	 *
	 * @return array
	 */
	public function getCombinedPermissions()
	{
		// the user specific assigned permissions
		$userSpecificPermissions = $this->permissions;

		// the user group permissions, if user has many groups, it will combine all the groups permissions
		$groupPermissions = $this->getGroupPermissions();

		foreach($userSpecificPermissions as $uPermission => $uValue)
		{
			// if the permission is inherit
			if( $uValue == User::PERMISSION_INHERIT )
			{
				// we will check if this permission exists in his group permissions,
				// if so, we will get the value from that group permissions and we will use it as its value
				// if it does not exist on its group permissions, just deny it
				if( array_key_exists($uPermission, $groupPermissions) )
				{
					$userSpecificPermissions[$uPermission] = $groupPermissions[$uPermission];
					unset($groupPermissions[$uPermission]);
				}
				else
				{
					$userSpecificPermissions[$uPermission] = User::PERMISSION_DENY;
				}
			}

			// if the value is allow or deny, we will check if this permission also existed on his group permissions
			// if it does, we will just remove it from there, we don't need it as it exist on users permissions
			// and it is more prioritize that permissions on the group
			else
			{
				if( array_key_exists($uPermission, $groupPermissions) )
				{
					unset($groupPermissions[$uPermission]);
				}
			}
		}

		return array_merge($userSpecificPermissions, $groupPermissions);
	}

	/**
	 * get all the permissions of this user, this is the combined permissions
	 * across all groups that the user is belong
	 *
	 * @return array
	 */
	public function getGroupPermissions()
	{
		$permissions = array();

		$groups = $this->groups;

		$groups->each(function($group) use (&$permissions)
		{
			foreach($group->permissions as $permission => $value)
			{
				// if the current permission is already on the permissions array
				// we will check if the value of the next same permission is a deny
				// if so, we will overwrite the value of the duplicated one
				// because if two groups has the same permission but different values,
				// the deny value will be prioritize
				if( array_key_exists($permission, $permissions) )
				{
					if( $value == User::PERMISSION_DENY )
					{
						$permissions[$permission] = $value;
					}
				}
				else
				{
					$permissions[$permission] = $value;
				}
			}
		});

		return $permissions;
	}

	/**
	 * logs last login date of the user
	 */
	public function logLastLogin()
	{
		$this->last_login = $this->freshTimestamp();
		$this->save();
	}

	/**
	 * get validation rules
	 *
	 * @return array
	 */
	public function getValidationRules()
	{
		return self::$rules;
	}

	public function scopeOfFirstName($query, $firstName)
	{
		if( $firstName === null || $firstName === '' ) return false;

		return $query->where('first_name','LIKE',"%{$firstName}%");
	}
	public function scopeOfLastName($query, $lastName)
	{
		if( $lastName === null || $lastName === '' ) return false;

		return $query->where('last_name','LIKE',"%{$lastName}%");
	}
	public function scopeOfEmail($query, $email)
	{
		if( $email === null || $email === '' ) return false;

		return $query->where('email','=',$email);
	}
}
