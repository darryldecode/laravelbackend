<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/2/2015
 * Time: 1:08 PM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Models;

use Darryldecode\Backend\Utility\Helpers;
use Illuminate\Database\Eloquent\Model as BaseModel;

class ContentTypeFormGroup extends BaseModel {

    static $requiredActionPermission = 'superuser';

    protected $table = 'content_type_form_group';

    protected $fillable = [
        'name',
        'form_name',
        'conditions',
        'fields',
        'content_type_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Content Custom Fields rules validation before persisting
     *
     * @var array
     */
    public static $rules = array(
        'name' => 'required',
        'form_name' => 'required',
        'fields' => 'required|array',
        'content_type_id' => 'required|numeric',
    );

    /**
     * serializes conditions attribute on the fly before saving to database
     *
     * @param $conditions
     */
    public function setConditionsAttribute($conditions)
    {
        $this->attributes['conditions'] = serialize($conditions);
    }

    /**
     * serializes fields attribute on the fly before saving to database
     *
     * @param $fields
     */
    public function setFieldsAttribute($fields)
    {
        $this->attributes['fields'] = serialize($fields);
    }

    /**
     * unserializes conditions attribute on the fly before saving to database
     *
     * @return mixed
     */
    public function getConditionsAttribute()
    {
        return (Helpers::is_serialized($this->attributes['conditions'])) ? unserialize($this->attributes['conditions']) : $this->attributes['conditions'];
    }

    /**
     * unserializes fields attribute on the fly before saving to database
     *
     * @return mixed
     */
    public function getFieldsAttribute()
    {
        return (Helpers::is_serialized($this->attributes['fields'])) ? unserialize($this->attributes['fields']) : $this->attributes['fields'];
    }

    /**
     * returns the content type where this custom field belongs
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contentType()
    {
        return $this->belongsTo('Darryldecode\Backend\Components\ContentBuilder\Models\ContentType','content_type_id');
    }
}