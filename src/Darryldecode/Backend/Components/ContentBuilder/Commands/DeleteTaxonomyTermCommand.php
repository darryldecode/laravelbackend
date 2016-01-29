<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 6/22/2015
 * Time: 2:32 AM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Commands;

use Darryldecode\Backend\Base\Commands\Command;

use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeTaxonomy;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeTaxonomyTerm;
use Illuminate\Contracts\Bus\SelfHandling;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Illuminate\Contracts\Events\Dispatcher;

class DeleteTaxonomyTermCommand extends Command implements SelfHandling {

    private $taxonomyId;

    private $termId;

    /**
     * @param null $taxonomyId
     * @param null $termId
     * @param bool $disablePermissionChecking
     */
    public function __construct($taxonomyId = null, $termId = null, $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->taxonomyId = $taxonomyId;
        $this->termId = $termId;
        $this->args = get_defined_vars();
        $this->disablePermissionChecking = $disablePermissionChecking;
    }

    /**
     * @param ContentType $contentType
     * @param ContentTypeTaxonomy $contentTypeTaxonomy
     * @param ContentTypeTaxonomyTerm $contentTypeTaxonomyTerm
     * @param Dispatcher $dispatcher
     * @return CommandResult
     */
    public function handle(ContentType $contentType, ContentTypeTaxonomy $contentTypeTaxonomy, ContentTypeTaxonomyTerm $contentTypeTaxonomyTerm, Dispatcher $dispatcher)
    {
        // in order to determine what permissions are needed to create
        // a taxonomy terms, we will get first what taxonomy the term is for
        // after we can get the taxonomy, we will then get what type the taxonomy
        // belong so we can verify if the user has permission for that type
        try {
            $taxonomy = $contentTypeTaxonomy->findOrFail($this->taxonomyId);
        } catch (\Exception $e){
            return new CommandResult(false, "Invalid Taxonomy.", null, 400);
        }

        try {
            $type = $contentType->findOrFail($taxonomy->content_type_id);
        } catch (\Exception $e){
            return new CommandResult(false, "Invalid Content Type.", null, 400);
        }

        try {
            $term = $contentTypeTaxonomyTerm->findOrFail($this->termId);
        } catch (\Exception $e){
            return new CommandResult(false, "Invalid Taxonomy Term.", null, 400);
        }

        // build the permissions needed
        $canManageOnThisType = $type->type.'.manage';

        // check if user has permission
        if( ! $this->disablePermissionChecking )
        {
            if( ! $this->user->hasAnyPermission([$canManageOnThisType]) )
            {
                return new CommandResult(false, "Not enough permission.", null, 403);
            }
        }

        // fire creating event
        $dispatcher->fire('taxonomyTerm.deleting', array($this->args));

        // detach all posts type related to this term first
        $term->contents()->detach();
        $term->delete();

        // fire creating event
        $dispatcher->fire('taxonomyTerm.deleted', array($taxonomy));

        // all good
        return new CommandResult(true, "Taxonomy Term successfully deleted.", null, 200);
    }
}