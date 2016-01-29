<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/23/2015
 * Time: 2:47 PM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;

class ContentTypeTaxonomyTerm extends BaseModel {

    protected $table = 'content_type_taxonomy_terms';

    protected $fillable = [
        'term',
        'slug',
        'content_type_taxonomy_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Content Taxonomy Term rules validation before persisting
     *
     * @var array
     */
    public static $rules = array(
        'term' => 'required',
        'slug' => 'required|unique:content_type_taxonomy_terms',
        'content_type_taxonomy_id' => 'required|numeric',
    );

    /**
     * the taxonomy where this term belongs
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function taxonomy()
    {
        return $this->belongsTo('Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeTaxonomy','content_type_taxonomy_id');
    }

    /**
     * the contents under this terms
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function contents()
    {
        return $this->belongsToMany('Darryldecode\Backend\Components\ContentBuilder\Models\Content','content_pivot_table','content_type_taxonomy_term_id');
    }
}