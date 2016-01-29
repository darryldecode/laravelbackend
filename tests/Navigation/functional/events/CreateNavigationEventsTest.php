<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/10/2015
 * Time: 8:32 PM
 */

use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Mockery as m;

class CreateNavigationEventsTest extends TestCase {

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

    public function testShouldFireCreatingAndCreatedEvents()
    {
        $user = $this->createUserAndLoggedIn(array('navigationBuilder.manage' => 1));

        // create event dispatcher mock and inject it to laravel application
        $eventDispatcherMock = $this->getMockBuilder('Illuminate\Events\Dispatcher')
            ->setMethods(array('fire'))
            ->getMock();

        // se expectations
        $eventDispatcherMock->expects($this->exactly(2))
            ->method('fire')
            ->withConsecutive(
                array($this->equalTo('navigationBuilder.creating'), $this->isType('array')),
                array($this->equalTo('navigationBuilder.created'), $this->isType('array'))
            );

        // inject to laravel "events" IoC alias
        $this->application['events'] = $eventDispatcherMock;

        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\Navigation\Commands\CreateNavigationCommand',
            array(
                'name' => 'SomeNavigationName',
                'data' => array(
                    'title' => 'home',
                    'class' => 'some-class',
                    'url' => 'www.url.com',
                    'items' => [],
                ),
            )
        );
    }

    protected function createUserAndLoggedIn($permissions = array())
    {
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => $permissions
        ));
        $this->application['auth']->loginUsingId($user->id);

        return $user;
    }
}