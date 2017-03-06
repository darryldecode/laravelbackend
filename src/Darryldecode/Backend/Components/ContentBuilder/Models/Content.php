<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/23/2015
 * Time: 2:39 PM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Models;

use Darryldecode\Backend\Utility\Helpers;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Content extends BaseModel {

    const CONTENT_PUBLISHED = 'published';
    const CONTENT_DRAFT = 'draft';

    protected $table = 'contents';

    protected $fillable = [
        'title',
        'body',
        'slug',
        'status',
        'permission_requirements',
        'author_id',
        'content_type_id',
        'misc_data',
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
        'title' => 'required',
        'body' => 'required',
        'slug' => 'required',
        'author_id' => 'required|numeric',
        'content_type_id' => 'required|numeric',
    );

    /**
     * the custom fields of this content
     *
     * @var array
     */
    protected $appends = ['custom_fields'];

    /**
     * @param $req
     */
    public function setPermissionRequirementsAttribute($req)
    {
        $this->attributes['permission_requirements'] = serialize($req);
    }

    /**
     * @return mixed
     */
    public function getPermissionRequirementsAttribute()
    {
        return (Helpers::is_serialized($this->attributes['permission_requirements'])) ? unserialize($this->attributes['permission_requirements']) : $this->attributes['permission_requirements'];
    }

    /**
     * @param $miscData
     */
    public function setMiscDataAttribute($miscData)
    {
        $this->attributes['misc_data'] = serialize($miscData);
    }

    /**
     * @return mixed
     */
    public function getMiscDataAttribute()
    {
        return (Helpers::is_serialized($this->attributes['misc_data'])) ? unserialize($this->attributes['misc_data']) : $this->attributes['misc_data'];
    }

    /**
     * @return array
     */
    public function getCustomFieldsAttribute()
    {
        return self::parseMetaData($this->metaData->toArray());
    }

    /**
     * parse meta data to associative array
     *
     * @param $metaData
     * @return array
     */
    public static function parseMetaData($metaData)
    {
        if( ! is_array($metaData) ) return false;

        $meta = array();

        foreach($metaData as $k => $v)
        {
            $meta[$v['form_group_name']][$v['key']] = $v['value'];
        }

        return $meta;
    }

    /**
     * the author of the content
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo('Darryldecode\Backend\Components\User\Models\User','author_id');
    }

    /**
     * the meta data of this content
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function metaData()
    {
        return $this->hasMany('Darryldecode\Backend\Components\ContentBuilder\Models\ContentMeta','content_id');
    }

    /**
     * the type of content
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo('Darryldecode\Backend\Components\ContentBuilder\Models\ContentType','content_type_id');
    }

    /**
     * returns the terms of the content
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function terms()
    {
        return $this->belongsToMany('Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeTaxonomyTerm','content_pivot_table','content_id');
    }

    /**
     * returns the revisions of this content
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function revisions()
    {
        return $this->hasMany('Darryldecode\Backend\Components\ContentBuilder\Models\ContentRevisions','content_id');
    }

    // SCOPES
    public function scopeOfStartDate($query, $startDate)
    {
        if( $startDate === false || $startDate === null ) return false;
        return $query->where('created_at','>=',$startDate);
    }
    public function scopeOfEndDate($query, $endDate)
    {
        if( $endDate === false || $endDate === null ) return false;
        return $query->where('created_at','<=',$endDate);
    }
    public function scopeOfTitle($query, $title)
    {
        if( $title === false || $title === null ) return false;
        return $query->where('title','LIKE',"%{$title}%");
    }
    public function scopeOfSlug($query, $slug)
    {
        if( $slug === false || $slug === null ) return false;
        return $query->where('slug','=',$slug);
    }
}