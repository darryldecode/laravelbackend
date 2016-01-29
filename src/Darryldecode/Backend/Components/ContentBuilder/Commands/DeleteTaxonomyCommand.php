<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/1/2015
 * Time: 2:26 PM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Commands;

use Darryldecode\Backend\Base\Commands\Command;

use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeTaxonomy;
use Illuminate\Contracts\Bus\SelfHandling;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Illuminate\Contracts\Events\Dispatcher;

class DeleteTaxonomyCommand extends Command implements SelfHandling {
    /**
     * @var null
     */
    private $taxonomyId;

    /**
     * @param null $taxonomyId
     * @param bool $disablePermissionChecking
     */
    public function __construct($taxonomyId = null, $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->taxonomyId = $taxonomyId;
        $this->args = get_defined_vars();
        $this->disablePermissionChecking = $disablePermissionChecking;
    }

    /**
     * @param ContentTypeTaxonomy $contentTypeTaxonomy
     * @param Dispatcher $dispatcher
     * @return CommandResult
     */
    public function handle(ContentTypeTaxonomy $contentTypeTaxonomy, Dispatcher $dispatcher)
    {
        // check if user has permission
        if( ! $this->disablePermissionChecking )
        {
            if( ! $this->user->hasAnyPermission(['contentBuilder.delete']) )
            {
                return new CommandResult(false, "Not enough permission.", null, 403);
            }
        }

        // fire creating event
        $dispatcher->fire('taxonomy.deleting', array($this->args));

        if( ! $taxonomy = $contentTypeTaxonomy->with(array('terms','terms.contents'))->find($this->taxonomyId) )
        {
            return new CommandResult(false, "Taxonomy not found.", null, 404);
        }

        // detach all contents that are related to its terms
        $taxonomy->terms->each(function($term)
        {
            $term->contents()->detach();
        });

        // delete taxonomy
        $taxonomy->delete();

        // fire creating event
        $dispatcher->fire('taxonomy.deleted', array($taxonomy));

        // all good
        return new CommandResult(true, "Taxonomy successfully deleted.", null, 200);
    }
}