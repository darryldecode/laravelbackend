<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/7/2015
 * Time: 6:38 PM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Commands;

use Darryldecode\Backend\Base\Commands\Command;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Darryldecode\Backend\Components\ContentBuilder\Models\Content;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentRevisions;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;

class UpdateContentCommand extends Command implements SelfHandling {
    /**
     * @var
     */
    private $id;
    /**
     * @var null
     */
    private $title;
    /**
     * @var null
     */
    private $body;
    /**
     * @var null
     */
    private $slug;
    /**
     * @var null
     */
    private $status;
    /**
     * @var null
     */
    private $authorId;
    /**
     * @var null
     */
    private $contentTypeId;
    /**
     * @var null
     */
    private $permissionRequirements;
    /**
     * @var null
     */
    private $taxonomies;
    /**
     * @var null
     */
    private $miscData;
    /**
     * @var null
     */
    private $metaData;

    /**
     * @param $id
     * @param null $title
     * @param null $body
     * @param null $slug
     * @param null $status
     * @param null $authorId
     * @param null $contentTypeId
     * @param null $permissionRequirements
     * @param null $taxonomies
     * @param null $miscData
     * @param null $metaData
     * @param bool $disablePermissionChecking
     */
    public function __construct($id,
                                $title = null,
                                $body = null,
                                $slug = null,
                                $status = null,
                                $authorId = null,
                                $contentTypeId = null,
                                $permissionRequirements = null,
                                $taxonomies = null,
                                $miscData = null,
                                $metaData = null,
                                $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->id = $id;
        $this->title = $title;
        $this->body = $body;
        $this->slug = $slug;
        $this->status = $status;
        $this->authorId = $authorId;
        $this->contentTypeId = $contentTypeId;
        $this->permissionRequirements = $permissionRequirements;
        $this->taxonomies = $taxonomies;
        $this->miscData = $miscData;
        $this->metaData = $metaData;
        $this->args = get_defined_vars();
        $this->disablePermissionChecking = $disablePermissionChecking;
    }

    /**
     * @param Content $content
     * @param ContentType $contentType
     * @param Dispatcher $dispatcher
     * @return CommandResult
     */
    public function handle(Content $content, ContentType $contentType, Dispatcher $dispatcher)
    {
        // get content available permissions
        try {
            $cType = $contentType->findOrFail($this->contentTypeId);
            $cTypeManage = $cType->type.'.manage';
        } catch(\Exception $e)
        {
            return new CommandResult(false, "Invalid Content Type.", null, 400);
        }

        // check if user has permission
        if( ! $this->disablePermissionChecking )
        {
            if( ! $this->user->hasAnyPermission([$cTypeManage]) )
            {
                return new CommandResult(false, "Not enough permission.", null, 403);
            }
        }

        if( ! $c = $content->find($this->id) )
        {
            return new CommandResult(false, "Content not found.", null, 404);
        };

        // fire event updating
        $dispatcher->fire($cType->type.'.updating', array($c, $this->args));

        // hold the current content so we can use it later if revisions is enabled
        $oldBody = $c->body;

        $c->title = $this->title ? $this->title : $c->title;
        $c->body = $this->body ? $this->body : $c->body;
        $c->slug = $this->slug ? $this->slug : $c->slug;
        $c->status = $this->status ? $this->status : $c->status;
        $c->permission_requirements = $this->permissionRequirements ? $this->permissionRequirements : $c->permission_requirements;
        $c->misc_data = $this->miscData ? $this->miscData : $c->misc_data;

        // taxonomy
        if( $this->taxonomies )
        {
            // detach all taxonomies first
            $c->terms()->detach();

            foreach($this->taxonomies as $termId => $value)
            {
                if( $value == true )
                {
                    $c->terms()->attach(array(
                        'content_type_taxonomy_term_id' => $termId
                    ));
                }
            }
        }

        // meta data
        if( $this->metaData )
        {
            // clear all meta data first
            $c->metaData()->delete();

            foreach($this->metaData as $formGroup => $formGroupData)
            {
                foreach($formGroupData as $metaKey => $metaValue)
                {
                    $c->metaData()->create(array(
                        'key' => $metaKey,
                        'value' => $metaValue,
                        'form_group_name' => $formGroup
                    ));
                }
            }
        }

        // save
        $c->save();

        // check if revisions is enabled so we can deal with it
        if( $cType->enable_revisions == ContentType::REVISIONS_ENABLED )
        {
            if( $oldBody != $c->body )
            {
                $c->revisions()->create(array(
                    'old_content' => $oldBody,
                    'new_content' => $c->body,
                    'author_id' => $this->user->id
                ));
            }
        }

        // fire event updated
        $dispatcher->fire($cType->type.'.updated', array($c));

        // return response
        return new CommandResult(true, "Content successfully updated.", $c, 200);
    }
}
