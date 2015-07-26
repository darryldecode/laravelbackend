<?php

namespace Darryldecode\Backend\Components\ContentBuilder\Commands;

use Darryldecode\Backend\Base\Commands\Command;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Darryldecode\Backend\Components\ContentBuilder\Models\Content;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Illuminate\Contracts\Bus\SelfHandling;
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
     * @param null $id
     * @param null $slug
     * @param null $title
     * @param bool $disablePermissionChecking
     * @param null|callable $queryHook
     */
    public function __construct($id = null, $slug = null, $title = null, $disablePermissionChecking = false, $queryHook = null)
    {
        parent::__construct();
        $this->id = $id;
        $this->slug = $slug;
        $this->title = $title;
        $this->args = func_get_args();
        $this->disablePermissionChecking = $disablePermissionChecking;
        $this->queryHook = $queryHook;
    }

    /**
     * @param Dispatcher $dispatcher
     * @param ContentType $contentType
     * @param Content $content
     * @return CommandResult
     */
    public function handle(Dispatcher $dispatcher, ContentType $contentType, Content $content)
    {
        // fire before query
        $dispatcher->fire('content.beforeQuery', array($this->args));

        // query
        $results = $this->query($contentType, $content);

        // fire after query
        $dispatcher->fire('content.afterQuery', array($results));

        // all good
        return new CommandResult(true, "Query content successful.", $results, 200);
    }

    /**
     * @param $contentType \Darryldecode\Backend\Components\ContentBuilder\Models\ContentType
     * @param $content \Darryldecode\Backend\Components\ContentBuilder\Models\Content
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    private function query($contentType, $content)
    {
        $q = $content->with(array(
            'terms',
            'author',
            'metaData',
            'type.formGroups',
            'revisions',
            'type'
        ));

        if( !is_null($this->queryHook) && (is_callable($this->queryHook)) )
        {
            if( $res = call_user_func($this->queryHook,$q) )
            {
                $q = $res;
            }
        }

        if( !is_null($this->id) && ($this->id != '') )
        {
            return $q->find($this->id);
        }
        else
        {
            return $q->ofSlug($this->slug)->ofTitle($this->title)->first();
        }
    }
}