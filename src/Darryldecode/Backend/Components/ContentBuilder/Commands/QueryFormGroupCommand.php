<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/3/2015
 * Time: 11:46 AM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Commands;

use Darryldecode\Backend\Base\Commands\Command;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeFormGroup;

class QueryFormGroupCommand extends Command implements SelfHandling {
    /**
     * @var null
     */
    private $contentTypeId;
    /**
     * @var bool
     */
    private $paginated;
    /**
     * @var int
     */
    private $perPage;

    /**
     * @param null $contentTypeId
     * @param bool $paginated
     * @param int $perPage
     * @param bool $disablePermissionChecking
     */
    public function __construct($contentTypeId = null, $paginated = true, $perPage = 6, $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->contentTypeId = $contentTypeId;
        $this->paginated = $paginated;
        $this->perPage = $perPage;
        $this->disablePermissionChecking = $disablePermissionChecking;
    }

    /**
     * @param Dispatcher $dispatcher
     * @param ContentTypeFormGroup $contentTypeFormGroup
     * @param ContentType $contentType
     * @return CommandResult
     */
    public function handle(Dispatcher $dispatcher, ContentTypeFormGroup $contentTypeFormGroup, ContentType $contentType)
    {
        if( is_null($this->contentTypeId) )
        {
            return $this->queryAll($contentTypeFormGroup, $dispatcher);
        }
        else
        {
            return $this->queryByContentType($contentType, $contentTypeFormGroup, $dispatcher);
        }
    }

    /**
     * @param ContentType $contentType
     * @param ContentTypeFormGroup $contentTypeFormGroup
     * @param Dispatcher $dispatcher
     * @return CommandResult
     */
    protected function queryByContentType($contentType, $contentTypeFormGroup, $dispatcher)
    {
        if( ! $cType = $contentType->find($this->contentTypeId) )
        {
            return new CommandResult(false, 'Content Type not found.', null, 400);
        }

        // build needed permissions
        // if the user has no permissions to manage or create under this Content Type,
        // the query should be forbidden
        $cTypeManage = $cType->type.'.manage';

        if( ! $this->disablePermissionChecking )
        {
            if( ! $this->user->hasAnyPermission([$cTypeManage,'contentBuilder.manage']) )
            {
                return new CommandResult(false, CommandResult::$responseForbiddenMessage, null, 403);
            }
        }

        // fire before query
        $dispatcher->fire('formGroup.beforeQuery', array($this->args));

        if( $this->paginated )
        {
            $res = $contentTypeFormGroup->with(array('contentType'))
                ->where('content_type_id', $this->contentTypeId)
                ->paginate($this->perPage);
        }
        else
        {
            $res = $contentTypeFormGroup->with(array('contentType'))
                ->where('content_type_id', $this->contentTypeId)
                ->get();
        }

        // fire after query
        $dispatcher->fire('formGroup.afterQuery', array($res));

        // all good
        return new CommandResult(true, "Query form groups command successful.", $res, 200);
    }

    /**
     * @param ContentTypeFormGroup $contentTypeFormGroup
     * @param Dispatcher $dispatcher
     * @return CommandResult
     */
    protected function queryAll($contentTypeFormGroup, $dispatcher)
    {
        if( ! $this->disablePermissionChecking )
        {
            if( ! $this->user->isSuperUser() )
            {
                return new CommandResult(false, CommandResult::$responseForbiddenMessage, null, 403);
            }
        }

        // fire before query
        $dispatcher->fire('formGroup.beforeQuery', array($this->args));

        if( $this->paginated )
        {
            $res = $contentTypeFormGroup->with(array('contentType'))
                ->paginate($this->perPage);
        }
        else
        {
            $res = $contentTypeFormGroup->with(array('contentType'))
                ->get();
        }

        // fire after query
        $dispatcher->fire('formGroup.afterQuery', array($res));

        // all good
        return new CommandResult(true, "Query form groups command successful.", $res, 200);
    }
}