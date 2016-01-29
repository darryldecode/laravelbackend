<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/1/2015
 * Time: 11:57 AM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Commands;


use Darryldecode\Backend\Base\Commands\Command;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeTaxonomy;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;

class QueryTermsByTaxonomyCommand extends Command implements SelfHandling {
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
        // fire before query
        $dispatcher->fire('taxonomyTerms.beforeQuery', array($this->args));

        // begin
        if( ! $taxonomy = $contentTypeTaxonomy->with('terms')->find($this->taxonomyId) )
        {
            return new CommandResult(false,"Taxonomy not found.",null,404);
        }

        // fire after query
        $dispatcher->fire('taxonomyTerms.afterQuery', array($this->args));

        // all good
        return new CommandResult(true, "Query terms by taxonomy command successful.", $taxonomy->terms, 200);
    }
}