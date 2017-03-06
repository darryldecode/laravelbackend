<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/23/2015
 * Time: 2:42 PM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Str;

class ContentType extends BaseModel {

    const REVISIONS_ENABLED = 1;
    const REVISIONS_DISABLED = 0;

    static $requiredActionPermission = 'superuser';

    protected $table = 'content_types';

    protected $fillable = [
        'type',
        'enable_revisions',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Content Type rules validation before persisting
     *
     * @var array
     */
    public static $rules = array(
        'type' => 'required',
    );

    /**
     * removes spaces before saving, neutralize !
     *
     * @param $type
     */
    public function setTypeAttribute($type)
    {
        $this->attributes['type'] = str_replace(array(' '),'_',$type);
    }

    /**
     * returns the contents that belong to this type
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contents()
    {
        return $this->hasMany('Darryldecode\Backend\Components\ContentBuilder\Models\Content','content_type_id');
    }

    /**
     * returns all the form groups of this type
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function formGroups()
    {
        return $this->hasMany('Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeFormGroup','content_type_id');
    }

    /**
     * returns the taxonomies of this type
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function taxonomies()
    {
        return $this->hasMany('Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeTaxonomy','content_type_id');
    }

    /**
     * the terms of the type under its taxonomy
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function terms()
    {
        return $this->hasManyThrough(
            'Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeTaxonomyTerm',
            'Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeTaxonomy',
            'content_type_id',
            'content_type_taxonomy_id'
        );
    }

    /**
     * determine if this content type enables revisions
     *
     * @return bool
     */
    public function isRevisionsEnabled()
    {
        return (bool) $this->enable_revisions;
    }

    public function scopeOfType($query, $type)
    {
        if( $type === null ) return false;

        return $query->whereRaw('lower(`type`) like ?',array(Str::lower($type)));
    }
}