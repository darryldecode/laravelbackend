<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/10/2015
 * Time: 8:03 PM
 */

namespace Darryldecode\Backend\Components\Navigation\Models;

use Darryldecode\Backend\Utility\Helpers;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Navigation extends BaseModel {

    protected $table = 'navigation';

    protected $fillable = [
        'name',
        'data',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Content rules validation before persisting
     *
     * @var array
     */
    public static $rules = array(
        'name' => 'required',
        'data' => 'required|array',
    );

    /**
     * @param $data
     */
    public function setDataAttribute($data)
    {
        $this->attributes['data'] = serialize($data);
    }

    /**
     * @return mixed
     */
    public function getDataAttribute()
    {
        return (Helpers::is_serialized($this->attributes['data'])) ? unserialize($this->attributes['data']) : $this->attributes['data'];
    }
}