<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/24/2015
 * Time: 4:07 PM
 */

namespace Darryldecode\Backend\Components\User\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Group extends BaseModel {

    const PERMISSION_ALLOW 	= 1;
    const PERMISSION_DENY 	= -1;

    /**
     * The valid permission values
     * 1 means allow and 0 means deny
     *
     * @var array
     */
    static $validPermissionValues = array(1, -1);

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'permissions'];

    /**
     * the rules of the Group for validation before persisting
     *
     * @var array
     */
    public static $rules = array(
        'name' => 'required',
        'permissions' => 'array',
    );

    /**
     * get validation rules
     *
     * @return array
     */
    public function getValidationRules()
    {
        return self::$rules;
    }

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
     * returns the users on this group
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('Darryldecode\Backend\Components\User\Models\User','user_group_pivot_table','group_id');
    }

    public function scopeOfName($query, $name)
    {
        if( $name === null ) return false;
        return $query->where('name','=',$name);
    }
}