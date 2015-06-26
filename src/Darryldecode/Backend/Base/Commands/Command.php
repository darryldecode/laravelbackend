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

	public function __construct()
    {
        $app = app();
        $this->app = $app;
        $this->user = $app['auth']->user();
    }
}
