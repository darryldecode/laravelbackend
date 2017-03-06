<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/23/2015
 * Time: 2:46 PM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;

class ContentTypeTaxonomy extends BaseModel {

    static $requiredActionPermission = 'superuser';

    protected $table = 'content_type_taxonomy';

    protected $fillable = [
        'taxonomy',
        'description',
        'content_type_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Content Taxonomy rules validation before persisting
     *
     * @var array
     */
    public static $rules = array(
        'taxonomy' => 'required',
        'content_type_id' => 'required|numeric',
    );

    /**
     * the content type this taxonomy belongs
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contentType()
    {
        return $this->belongsTo('Darryldecode\Backend\Components\ContentBuilder\Models\ContentType','content_type_id');
    }

    /**
     * the terms of this taxonomy
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function terms()
    {
        return $this->hasMany('Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeTaxonomyTerm','content_type_taxonomy_id');
    }
}