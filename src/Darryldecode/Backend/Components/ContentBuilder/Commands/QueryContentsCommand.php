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
     * @var null
     */
    private $startDate;
    /**
     * @var null
     */
    private $endDate;

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
     * @param null $startDate
     * @param null $endDate
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
                                $disablePermissionChecking = false,
                                $startDate = null,
                                $endDate = null)
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
        $this->startDate = $startDate;
        $this->endDate = $endDate;
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
            $tax = $this->extractTerms($this->terms);

            if(count($tax) > 0)
            {
                foreach($tax as $k => $v)
                {
                    $q->whereHas('terms', function ($q) use ($k, $v)
                    {
                        $q->whereHas('taxonomy', function ($q) use ($k)
                        {
                            $q->where('taxonomy', $k);
                        });

                        if( is_string($v) )
                        {
                            $q->where('slug',$v);
                        }
                        else
                        {
                            $q->whereIn('slug',$v);
                        }
                    });
                }
            }
        }

        // setup date ranges
        if( !is_null($this->startDate) && ($this->startDate!='') )
        {
            $q->ofStartDate($this->startDate);
        }
        if( !is_null($this->endDate) && ($this->endDate!='') )
        {
            $q->ofEndDate($this->endDate);
        }

        // decide whether request wants paginated version or not
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
     *
     * Example.
     *
     * from:
     *  terms=:Size|small:Color|blue:Availability|yes:Size|medium
     * to:
     *  array(
     *      Size => array('small','medium'),
     *      Color => array('blue'),
     *      Availability => array('yes'),
     * )
     *
     * @param $terms
     * @return array
     */
    private function extractTerms($terms)
    {
        if( is_array($terms) ) return $terms;

        $t = array();

        $terms = explode(':', trim($terms,':'));

        foreach($terms as $tx)
        {
            $x = explode('|',$tx);

            if( array_key_exists($x[0], $t) )
            {
                array_push($t[$x[0]],$x[1]);
            }
            else
            {
                $t[$x[0]] = array($x[1]);
            }
        }

        return $t;
    }
}