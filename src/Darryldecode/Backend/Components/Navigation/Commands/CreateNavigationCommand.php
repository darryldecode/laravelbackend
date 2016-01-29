<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/10/2015
 * Time: 8:09 PM
 */

namespace Darryldecode\Backend\Components\Navigation\Commands;

use Darryldecode\Backend\Base\Commands\Command;

use Darryldecode\Backend\Base\Commands\CommandResult;
use Darryldecode\Backend\Components\Navigation\Models\Navigation;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Validation\Factory;

class CreateNavigationCommand extends Command implements SelfHandling {
    /**
     * @var null
     */
    private $name;
    /**
     * @var array
     */
    private $data;

    /**
     * @param null $name
     * @param array $data
     * @param bool $disablePermissionChecking
     */
    public function __construct($name = null, $data = array(), $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->name = $name;
        $this->data = $data;
        $this->args = get_defined_vars();
        $this->disablePermissionChecking = $disablePermissionChecking;
    }

    /**
     * @param Navigation $navigation
     * @param Factory $validator
     * @param Dispatcher $dispatcher
     * @return CommandResult
     */
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

        // fire before create event
        $dispatcher->fire('navigationBuilder.creating', array($this->args));

        // begin create
        $nav = $navigation->create(array(
            'name' => $this->name,
            'data' => $this->data
        ));

        if( ! $nav ) new CommandResult(false, "Failed to create nav.", null, 400);

        // fire after create
        $dispatcher->fire('navigationBuilder.created', array($nav));

        // all good
        return new CommandResult(true, "Navigation successfully created.", $nav, 201);
    }
}