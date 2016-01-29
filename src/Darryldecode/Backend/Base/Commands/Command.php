<?php namespace Darryldecode\Backend\Base\Commands;

abstract class Command {

    /**
     * @var \Darryldecode\Backend\Components\User\Models\User
     */
    protected $user;

    /**
     * the application
     *
     * @var
     */
    protected $app;

    /**
     * the array of args of the command
     *
     * @var
     */
    protected $args;

    /**
     * disable the permission checking on a command, this will be helpful
     * when the commands are being used as an API or something custom that does not need
     * to check a user permission. Just in case you need it to work freely
     *
     * @var bool
     */
    protected $disablePermissionChecking = false;

	public function __construct()
    {
        $app = app();
        $this->app = $app;
        $this->user = $app['auth']->user();
    }
}
