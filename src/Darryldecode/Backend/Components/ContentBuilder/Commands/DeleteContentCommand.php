<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/8/2015
 * Time: 2:08 PM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Commands;

use Darryldecode\Backend\Base\Commands\Command;

use Darryldecode\Backend\Base\Commands\CommandResult;
use Darryldecode\Backend\Components\ContentBuilder\Models\Content;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;

class DeleteContentCommand extends Command implements SelfHandling {
    /**
     * @var
     */
    private $id;

    /**
     * @param $id
     * @param bool $disablePermissionChecking
     */
    public function __construct($id, $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->id = $id;
        $this->disablePermissionChecking = $disablePermissionChecking;
    }

    /**
     * @param Content $content
     * @param Dispatcher $dispatcher
     * @return CommandResult
     */
    public function handle(Content $content, Dispatcher $dispatcher)
    {
        // get the content
        if( ! $c = $content->with(array('type','metaData'))->find($this->id) )
        {
            return new CommandResult(false, "Content not found.", null, 404);
        }

        // get content available permissions
        $cTypeDeletePermission = $c->type->type.'.delete';

        // check if user has permission
        if( ! $this->disablePermissionChecking )
        {
            if( ! $this->user->hasAnyPermission([$cTypeDeletePermission]) )
            {
                return new CommandResult(false, "Not enough permission.", null, 403);
            }
        }

        // fire deleting event
        $dispatcher->fire($c->type->type.'.deleting', array($c));

        // begin delete
        $c->metaData()->delete();
        $c->delete();

        // fire deleted event
        $dispatcher->fire($c->type->type.'.deleted', array($c));

        // all good
        return new CommandResult(true, $c->type->type.' content successfully deleted.', null, 200);
    }
}