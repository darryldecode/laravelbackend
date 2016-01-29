<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/5/2015
 * Time: 9:13 PM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Commands;

use Darryldecode\Backend\Base\Commands\Command;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeFormGroup;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;

class DeleteFormGroupCommand extends Command implements SelfHandling {
    /**
     * @var null
     */
    private $id;

    /**
     * @param null $id
     * @param bool $disablePermissionChecking
     */
    public function __construct($id = null, $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->id = $id;
        $this->args = get_defined_vars();
        $this->disablePermissionChecking = $disablePermissionChecking;
    }

    /**
     * @param ContentTypeFormGroup $contentTypeFormGroup
     * @param Dispatcher $dispatcher
     * @return CommandResult
     */
    public function handle(ContentTypeFormGroup $contentTypeFormGroup, Dispatcher $dispatcher)
    {
        // check if user has permission
        if( ! $this->disablePermissionChecking )
        {
            if( ! $this->user->hasAnyPermission(['contentBuilder.delete']) )
            {
                return new CommandResult(false, "Not enough permission.", null, 403);
            }
        }

        if( ! $formGroup = $contentTypeFormGroup->find($this->id) )
        {
            return new CommandResult(false, "Form Group Not Found.", null, 404);
        }

        // fire before delete event
        $dispatcher->fire('formGroup.deleting', array($formGroup));

        $formGroup->delete();

        // fire after delete event
        $dispatcher->fire('formGroup.deleted', array($formGroup));

        // all good
        return new CommandResult(true, "Form group successfully deleted.", null, 200);
    }
}