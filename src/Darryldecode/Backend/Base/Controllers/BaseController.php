<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/28/2015
 * Time: 8:24 AM
 */

namespace Darryldecode\Backend\Base\Controllers;

use App\Http\Controllers\Controller;

abstract class BaseController extends Controller {

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
}