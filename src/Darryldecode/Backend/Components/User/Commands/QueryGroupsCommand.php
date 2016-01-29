<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/28/2015
 * Time: 8:27 AM
 */

namespace Darryldecode\Backend\Components\User\Commands;

use Darryldecode\Backend\Base\Commands\CommandResult;
use Darryldecode\Backend\Components\User\Models\Group;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;
use Darryldecode\Backend\Base\Commands\Command;

class QueryGroupsCommand extends Command implements SelfHandling {
    /**
     * @var null
     */
    private $name;
    /**
     * @var array
     */
    private $with;
    /**
     * @var bool
     */
    private $paginate;
    /**
     * @var int
     */
    private $perPage;

    /**
     * @param null $name
     * @param array $with
     * @param bool $paginate
     * @param int $perPage
     * @param bool $disablePermissionChecking
     */
    public function __construct($name = null, $with = array(), $paginate = true, $perPage = 15, $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->name = $name;
        $this->with = $with;
        $this->paginate = $paginate;
        $this->perPage = $perPage;
        $this->args = get_defined_vars();
        $this->disablePermissionChecking = $disablePermissionChecking;
    }

    /**
     * @param \Darryldecode\Backend\Components\User\Models\Group $group
     * @param \Illuminate\Contracts\Events\Dispatcher $dispatcher
     * @return \Darryldecode\Backend\Base\Commands\CommandResult
     */
    public function handle(Group $group, Dispatcher $dispatcher)
    {
        // check user permission
        if( ! $this->disablePermissionChecking )
        {
            if( ! $this->user->hasAnyPermission(['user.manage']) )
            {
                return new CommandResult(false, CommandResult::$responseForbiddenMessage, null, 403);
            }
        }

        // fire before query event
        $dispatcher->fire('groups.beforeQuery', array($this->args));

        // begin
        $q = $group->with(array_merge(array('users'),$this->with))->ofName($this->name);

        // give paginated results if told so
        if( $this->paginate )
        {
            $results = $q->paginate($this->perPage);
        }
        else
        {
            $results = $q->get();
        }

        // fire after query event
        $dispatcher->fire('groups.afterQuery', array($results));

        // all good
        return new CommandResult(true, "Query groups command successful.", $results, 200);
    }
}