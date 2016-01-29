<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/24/2015
 * Time: 3:57 PM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;

class ContentRevisions extends BaseModel {

    protected $table = 'content_revisions';

    protected $fillable = [
        'old_content',
        'new_content',
        'content_id',
        'author_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Content Revision rules validation before persisting
     *
     * @var array
     */
    public static $rules = array(
        'old_content' => 'required',
        'new_content' => 'required',
        'content_id' => 'required|int',
        'author_id' => 'required|int',
    );

    /**
     * the content this revisions belongs
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function content()
    {
        return $this->belongsTo('Darryldecode\Backend\Components\ContentBuilder\Models\Content','content_id');
    }
}