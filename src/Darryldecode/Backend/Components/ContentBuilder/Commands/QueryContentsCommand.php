<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/7/2015
 * Time: 12:09 PM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Commands;

use Darryldecode\Backend\Base\Commands\Command;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Darryldecode\Backend\Components\ContentBuilder\Models\Content;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;

class QueryContentsCommand extends Command implements SelfHandling {
    /**
     * @var null
     */
    private $type;
    /**
     * @var array
     */
    private $terms;
    /**
     * @var array
     */
    private $meta;
    /**
     * @var bool
     */
    private $paginated;
    /**
     * @var int
     */
    private $perPage;
    /**
     * @var string
     */
    private $sortBy;
    /**
     * @var string
     */
    private $sortOrder;
    /**
     * @var string
     */
    private $status;
    /**
     * @var null
     */
    private $authorId;

    /**
     * @param null $type
     * @param string $status
     * @param null $authorId
     * @param array $terms
     * @param array $meta
     * @param bool $paginated
     * @param int $perPage
     * @param string $sortBy
     * @param string $sortOrder
     * @param bool $disablePermissionChecking
     */
    public function __construct($type = null,
                                $status = 'any',
                                $authorId = null,
                                $terms = array(),
                                $meta = array(),
                                $paginated = true,
                                $perPage = 8,
                                $sortBy = 'created_at',
                                $sortOrder = 'DESC',
                                $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->type = $type;
        $this->terms = $terms;
        $this->meta = $meta;
        $this->paginated = $paginated;
        $this->perPage = $perPage;
        $this->sortBy = $sortBy;
        $this->sortOrder = $sortOrder;
        $this->status = $status;
        $this->authorId = $authorId;
        $this->args = get_defined_vars();
        $this->disablePermissionChecking = $disablePermissionChecking;
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
        $dispatcher->fire('contents.beforeQuery', array($this->args));

        // query
        $results = $this->query($contentType, $content);

        // fire after query
        $dispatcher->fire('contents.afterQuery', array($results));

        // all good
        return new CommandResult(true, "Query contents successful.", $results, 200);
    }

    /**
     * Query By content type
     *
     * @param ContentType $contentType
     * @param Content $content
     * @return mixed
     */
    protected function query($contentType, $content)
    {
        $q = $content->with(array(
            'terms',
            'author',
            'metaData',
            'type.formGroups',
            'revisions',
            'type'
        ));

        // check if there is status provided
        if( ($this->status) && ($this->status != 'any') )
        {
            $q->where('status', $this->status);
        }

        // check if author ID is provided
        if( $this->authorId )
        {
            $q->where('author_id', $this->authorId);
        }

        // check if type is provided so we can include it in our query conditions
        if( !is_null($this->type) && ($this->type != '') )
        {
            if( is_numeric($this->type) )
            {
                $cType = $contentType->find($this->type);
            }
            else
            {
                $cType = $contentType->with(array())->where('type',$this->type)->first();
            }

            if( $cType )
            {
                $q->whereHas('type', function ($q) use ($cType)
                {
                    $q->where('type',$cType->type);
                });
            }
        }

        // check if terms are provided so we can include it in query conditions
        if( !is_null($this->terms) && ($this->terms != '') )
        {
            // if terms is in array format, the request probably don't contain "terms" query string
            // because an empty [] is set to it as default when it is not provided
            if( !is_array($this->terms) )
            {
                $q->whereHas('terms', function ($q)
                {
                    $terms = $this->extractTerms($this->terms);

                    if( count($terms) > 1 )
                    {
                        $q->whereIn('slug', $terms);
                    }
                    elseif( count($terms) == 1 )
                    {
                        $q->where('slug', $terms[0]);
                    }
                });
            }
        }

        if( $this->paginated )
        {
            $res = $q->paginate($this->perPage);
        }
        else
        {
            $res = $q->get();
        }

        return $res;
    }

    /**
     * Just extracts the terms from param
     * Ex. terms=term1:term2:term3 to array(term1,term2,term3)
     *
     * @param $terms
     * @return array
     */
    private function extractTerms($terms)
    {
        if( is_array($terms) ) return $terms;

        return explode(':',$terms);
    }
}