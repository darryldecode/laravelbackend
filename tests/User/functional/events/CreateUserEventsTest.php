<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/26/2015
 * Time: 7:46 AM
 */

use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Mockery as m;

class CreateUserEventsTest extends TestCase {

    protected $application;

    protected $faker;

    /**
     * @var Illuminate\Contracts\Bus\Dispatcher
     */
    protected $commandDispatcher;

    public function setUp()
    {
        $this->faker = Faker::create();
        $this->application = $this->createApplication();
        $this->application['config']->set('database.connections.sqlite.database',':memory:');
        $this->application['config']->set('session.driver','array');
        $this->application['db']->setDefaultConnection('sqlite');
        $this->application->make('Illuminate\Contracts\Console\Kernel')->call('migrate');
        $this->commandDispatcher = $this->application->make('Illuminate\Contracts\Bus\Dispatcher');
    }

    public function tearDown()
    {

    }

    public function testCreatingAndCreatedEvents()
    {
        // create user and logged in
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'user.manage' => 1
            )
        ));
        $this->application['auth']->loginUsingId($user->id);

        /// create event dispatcher mock and inject it to laravel application
        $eventDispatcherMock = $this->getMockBuilder('Illuminate\Events\Dispatcher')
            ->setMethods(array('fire'))
            ->getMock();

        // set expectations
        $eventDispatcherMock->expects($this->exactly(2))
            ->method('fire')
            ->withConsecutive(
                array($this->equalTo('user.creating'), $this->isType('array')),
                array($this->equalTo('user.created'), $this->isType('array'))
            );

        // inject to laravel "events" IoC alias
        $this->application['events'] = $eventDispatcherMock;

        // dummy request, required first name
        $request = Request::create('','GET',array(
            'firstName' => 'Darryl',
            'lastName' => 'Fernandez',
            'email' => 'engrdarrylfernandez@gmail.com',
            'password' => 'password',
            'permissions' => array(),
            'groups' => array(),
        ));

        // begin
        $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\User\Commands\CreateUserCommand',
            $request
        );
    }
}