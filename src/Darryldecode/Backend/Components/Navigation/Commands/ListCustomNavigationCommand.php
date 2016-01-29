<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/10/2015
 * Time: 9:39 PM
 */

namespace Darryldecode\Backend\Components\Navigation\Commands;

use Darryldecode\Backend\Base\Commands\Command;

use Darryldecode\Backend\Base\Commands\CommandResult;
use Darryldecode\Backend\Components\Navigation\Models\Navigation;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;

class ListCustomNavigationCommand extends Command implements SelfHandling {
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
    private $orderBy;
    /**
     * @var string
     */
    private $orderSort;
    /**
     * @var null
     */
    private $id;

    /**
     * @param int|null $id
     * @param bool $paginated
     * @param int $perPage
     * @param string $orderBy
     * @param string $orderSort
     * @param bool $disablePermissionChecking
     */
    public function __construct($id = null,
                                $paginated = true,
                                $perPage = 8,
                                $orderBy = 'created_at',
                                $orderSort = 'DESC',
                                $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->paginated = $paginated;
        $this->perPage = $perPage;
        $this->orderBy = $orderBy;
        $this->orderSort = $orderSort;
        $this->args = get_defined_vars();
        $this->disablePermissionChecking = $disablePermissionChecking;
        $this->id = $id;
    }

    /**
     * @param Navigation $navigation
     * @param Dispatcher $dispatcher
     * @return CommandResult
     */
    public function handle(Navigation $navigation, Dispatcher $dispatcher)
    {
        // check if user has permission
        if( ! $this->disablePermissionChecking )
        {
            if( ! $this->user->hasAnyPermission(['navigationBuilder.manage']) )
            {
                return new CommandResult(false, "Not enough permission.", null, 403);
            }
        }

        // fire before create event
        $dispatcher->fire('navigationBuilder.beforeQuery', array($this->args));

        if( $this->id && ($this->id!='') )
        {
            if( ! $res = $navigation->with(array())->find($this->id) )
            {
                return new CommandResult(false, "Navigation does not exist.", null, 404);
            }
        }
        else
        {
            if( $this->paginated )
            {
                $res = $navigation->with(array())->paginate($this->perPage);
            }
            else
            {
                $res = $navigation->all();
            }
        }

        // fire after create
        $dispatcher->fire('navigationBuilder.afterQuery', array($this->args));

        // all good
        return new CommandResult(true, "List custom navigation command successful.", $res, 200);
    }
}