<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/3/2015
 * Time: 6:26 PM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Commands;

use Darryldecode\Backend\Base\Commands\Command;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Illuminate\Contracts\Bus\SelfHandling;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Illuminate\Contracts\Events\Dispatcher;

class DeleteContentTypeCommand extends Command implements SelfHandling {
    /**
     * @var
     */
    private $contentTypeId;

    /**
     * @param $contentTypeId
     * @param bool $disablePermissionChecking
     */
    public function __construct($contentTypeId = null, $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->contentTypeId = $contentTypeId;
        $this->args = get_defined_vars();
        $this->disablePermissionChecking = $disablePermissionChecking;
    }

    /**
     * @param Dispatcher $dispatcher
     * @param ContentType $contentType
     * @return CommandResult
     */
    public function handle(Dispatcher $dispatcher, ContentType $contentType)
    {
        // check if user has permission
        if( ! $this->disablePermissionChecking )
        {
            if( ! $this->user->hasAnyPermission(['contentBuilder.delete']) )
            {
                return new CommandResult(false, "Not enough permission.", null, 403);
            }
        }

        if( ! $cType = $contentType->find($this->contentTypeId) )
        {
            return new CommandResult(false, "Content type not found.", null, 400);
        }

        // prevent deletion if content Type as contents
        if( $this->contentTypeHasContents($cType) )
        {
            return new CommandResult(false, "Content type has contents. Delete Contents first.", null, 400);
        }

        // fire before delete
        $dispatcher->fire('contentType.deleting', array($this->args));

        $cType->delete();

        // fire before delete
        $dispatcher->fire('contentType.deleted', array($cType));

        // all good
        return new CommandResult(true, "Content type successfully deleted.", null, 200);
    }

    /**
     * @param $cType
     * @return bool
     */
    protected function contentTypeHasContents($cType)
    {
        return ($cType->contents->count() > 0);
    }
}