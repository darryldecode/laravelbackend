<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/28/2015
 * Time: 8:24 AM
 */

namespace Darryldecode\Backend\Base\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class BaseController extends Controller {

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var \Darryldecode\Backend\Components\User\Models\User
     */
    protected $user;

    public function __construct()
    {
        $app = app();
        $this->app = $app;
        $this->user = $app['auth']->user();
    }

    /**
     * The before backend access hook
     */
    public function triggerBeforeBackendHook()
    {
        $hook = config('backend.backend.before_backend_access');

        if( is_callable($hook) )
        {
            $hook($this->user);
        }
    }

    public function dispatchFromArray($command, array $array)
    {
        return app('Darryldecode\Backend\Base\Contracts\Bus\Dispatcher')->dispatchFromArray($command,$array);
    }

    public function dispatchFrom($command, \ArrayAccess $source, array $extras)
    {
        return app('Darryldecode\Backend\Base\Contracts\Bus\Dispatcher')->dispatchFrom($command,$source,$extras);
    }
}