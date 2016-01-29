<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/23/2015
 * Time: 2:47 PM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Models;

use Darryldecode\Backend\Utility\Helpers;
use Illuminate\Database\Eloquent\Model as BaseModel;

class ContentMeta extends BaseModel {

    protected $table = 'content_meta';

    protected $fillable = [
        'key',
        'value',
        'form_group_name',
        'content_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Content Meta rules validation before persisting
     *
     * @var array
     */
    public static $rules = array(
        'key' => 'required',
        'value' => 'required',
        'content_id' => 'required|int',
    );

    /**
     * @param $value
     */
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = (is_array($value)) ? serialize($value) : $value;
    }

    /**
     * @return mixed
     */
    public function getValueAttribute()
    {
        return (Helpers::is_serialized($this->attributes['value'])) ? unserialize($this->attributes['value']) : $this->attributes['value'];
    }
}