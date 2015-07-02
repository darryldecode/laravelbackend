<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/28/2015
 * Time: 7:53 AM
 */

use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Mockery as m;

class QueryUsersCommandEventsTest extends TestCase {

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

    public function testBeforeAndAfterQueryEvents()
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
                array($this->equalTo('user.beforeQuery'), $this->isType('array')),
                array($this->equalTo('user.afterQuery'), $this->isType('array'))
            );

        // inject to laravel "events" IoC alias
        $this->application['events'] = $eventDispatcherMock;

        // dummy request, required first name
        $request = Request::create('','GET',array(
            'firstName' => null,
            'lastName' => null,
            'email' => null,
            'groupId' => null,
            'with' => array(),
            'orderBy' => 'created_at',
            'orderSort' => 'ASC',
            'paginated' => true,
            'perPage' => 15,
        ));

        // begin
        $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\User\Commands\QueryUsersCommand',
            $request
        );
    }
}