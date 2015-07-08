<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/30/2015
 * Time: 9:10 PM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Commands;

use Darryldecode\Backend\Base\Commands\Command;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;

class QueryContentTypeCommand extends Command implements SelfHandling {
    /**
     * @var null
     */
    private $type;

    /**
     * @param null|string $type
     * @param bool $disablePermissionChecking
     */
    public function __construct($type = null, $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->type = $type;
        $this->args = get_defined_vars();
        $this->disablePermissionChecking = $disablePermissionChecking;
    }

    /**
     * @param ContentType $contentType
     * @param Dispatcher $dispatcher
     * @return CommandResult
     */
    public function handle(ContentType $contentType, Dispatcher $dispatcher)
    {
        // begin before query
        $dispatcher->fire('contentType.beforeQuery', array($this->args));

        // begin query
        $results = $contentType->with(array('terms.taxonomy','taxonomies','taxonomies.terms','formGroups'))->ofType($this->type)->get();

        // begin after query
        $dispatcher->fire('contentType.afterQuery', array($this->args));

        // all good
        return new CommandResult(true, "Query content types successful.", $results, 200);
    }
}