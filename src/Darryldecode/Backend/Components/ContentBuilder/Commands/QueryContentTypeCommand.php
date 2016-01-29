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

        // check if has permission
        if( ! $this->disablePermissionChecking )
        {
            // if $type->type is not provided, the request referrer should be from
            // the admin UI Content Type Builder component.
            // so we will check if the user has permission (contentBuilder.manage)
            // on the other hand,
            // if $type->type is provided, we will check if user has permission to manage that type
            if( ! is_null($this->type) && ($this->type!='') )
            {
                if( ! $this->user->hasAnyPermission([$this->type.'.manage']) )
                {
                    return new CommandResult(false, "Not enough permission.", null, 403);
                }
            }
            else
            {
                if( ! $this->user->hasAnyPermission(['contentBuilder.manage']) )
                {
                    return new CommandResult(false, "Not enough permission.", null, 403);
                }
            }
        }

        // begin query
        $results = $contentType->with(array('terms.taxonomy','taxonomies','taxonomies.terms','formGroups'))->ofType($this->type)->get();

        // begin after query
        $dispatcher->fire('contentType.afterQuery', array($this->args));

        // all good
        return new CommandResult(true, "Query content types successful.", $results, 200);
    }
}