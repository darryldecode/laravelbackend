<?php

namespace Darryldecode\Backend\Components\ContentBuilder\Commands;

use Darryldecode\Backend\Base\Commands\Command;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Darryldecode\Backend\Components\ContentBuilder\Models\Content;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;

class QueryContentCommand extends Command implements SelfHandling {
    /**
     * @var null
     */
    private $id;
    /**
     * @var null
     */
    private $slug;
    /**
     * @var null
     */
    private $title;
    /**
     * @var null
     */
    private $queryHook;
    /**
     * @var array
     */
    private $with;

    /**
     * @param null $id
     * @param null $slug
     * @param null $title
     * @param array $with
     * @param bool $disablePermissionChecking
     * @param null|callable $queryHook
     */
    public function __construct($id = null, $slug = null, $title = null, $with = array(), $disablePermissionChecking = false, $queryHook = null)
    {
        parent::__construct();
        $this->id = $id;
        $this->slug = $slug;
        $this->title = $title;
        $this->args = func_get_args();
        $this->disablePermissionChecking = $disablePermissionChecking;
        $this->queryHook = $queryHook;
        $this->with = $with;
    }

    /**
     * @param Dispatcher $dispatcher
     * @param ContentType $contentType
     * @param Content $content
     * @param Repository $config
     * @return CommandResult
     */
    public function handle(Dispatcher $dispatcher, ContentType $contentType, Content $content, Repository $config)
    {
        // fire before query
        $dispatcher->fire('content.beforeQuery', array($this->args));

        /** @todo add permission check here: {contentType}.manage */

        // query
        $results = $this->query($contentType, $content, $config);

        // fire after query
        $dispatcher->fire('content.afterQuery', array($results));

        // all good
        return new CommandResult(true, "Query content successful.", $results, 200);
    }

    /**
     * @param $contentType \Darryldecode\Backend\Components\ContentBuilder\Models\ContentType
     * @param $content \Darryldecode\Backend\Components\ContentBuilder\Models\Content
     * @param $config \Illuminate\Contracts\Config\Repository
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    private function query($contentType, $content, $config)
    {
        // prepare content model used
        $content = $this->createContentModel($content, $config);

        $q = $content->with(array_merge(array(
            'terms',
            'author',
            'metaData',
            'type.formGroups',
            'revisions',
            'type'
        ),$this->with));

        if( !is_null($this->queryHook) && (is_callable($this->queryHook)) )
        {
            if( $res = call_user_func($this->queryHook,$q) )
            {
                $q = $res;
            }
        }

        if( !is_null($this->id) && ($this->id != '') )
        {
            $result = $q->find($this->id);
        }
        else
        {
            $result = $q->ofSlug($this->slug)->ofTitle($this->title)->first();
        }

        if( ! $this->disablePermissionChecking )
        {
            $requiredPermission = $result->type->type.'.manage';

            if( ! $this->user->hasAnyPermission([$requiredPermission]) )
            {
                return new CommandResult(false, "Not enough permission.", null, 403);
            }
        }

        return $result;
    }

    /**
     * @param $content \Darryldecode\Backend\Components\ContentBuilder\Models\Content
     * @param $config \Illuminate\Contracts\Config\Repository
     * @return mixed
     */
    protected function createContentModel($content, $config)
    {
        if( ! $contentModelUsed = $config->get('backend.backend.content_model') )
        {
            return $content;
        };

        $contentModelUsed = new $contentModelUsed();

        if( $contentModelUsed instanceof Content )
        {
            return $contentModelUsed;
        }

        return $content;
    }
}