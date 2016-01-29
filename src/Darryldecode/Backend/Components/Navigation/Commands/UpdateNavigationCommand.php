<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 6/23/2015
 * Time: 7:25 PM
 */

namespace Darryldecode\Backend\Components\Navigation\Commands;

use Darryldecode\Backend\Base\Commands\Command;

use Darryldecode\Backend\Base\Commands\CommandResult;
use Darryldecode\Backend\Components\Navigation\Models\Navigation;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Validation\Factory;

class UpdateNavigationCommand extends Command implements SelfHandling {

    /**
     * The ID of the navigation to be updated
     *
     * @var int|null
     */
    private $id;

    /**
     * the name of the navigation
     *
     * @var string
     */
    private $name;

    /**
     * the data of the navigation
     *
     * @var array
     */
    private $data = array();

    /**
     * @param null $id
     * @param null $name
     * @param null $data
     * @param bool $disablePermissionChecking
     */
    public function __construct($id = null, $name = null, $data = null, $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->id = $id;
        $this->name = $name;
        $this->data = $data;
        $this->args = func_get_args();
        $this->disablePermissionChecking = $disablePermissionChecking;
    }

    public function handle(Navigation $navigation, Factory $validator, Dispatcher $dispatcher)
    {
        // check if user has permission
        if( ! $this->disablePermissionChecking )
        {
            if( ! $this->user->hasAnyPermission(['navigationBuilder.manage']) )
            {
                return new CommandResult(false, "Not enough permission.", null, 403);
            }
        }

        // validate data
        $validationResult = $validator->make(array(
            'name' => $this->name,
            'data' => $this->data,
        ), Navigation::$rules);

        if( $validationResult->fails() )
        {
            return new CommandResult(false, $validationResult->getMessageBag()->first(), null, 400);
        }

        if( ! $nav = $navigation->find($this->id) )
        {
            return new CommandResult(false, 'Navigation does not exist.', null, 400);
        }

        // fire before create event
        $dispatcher->fire('navigationBuilder.updating', array($nav, $this->args));

        $nav->name = $this->name;
        $nav->data = $this->data;
        $nav->save();

        // fire after create
        $dispatcher->fire('navigationBuilder.updated', array($nav, $this->args));

        // all good
        return new CommandResult(true, "Navigation successfully updated.", $nav, 200);
    }
}