<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 6/24/2015
 * Time: 6:27 PM
 */

use Darryldecode\Backend\Components\Navigation\Models\Navigation;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Mockery as m;

class UpdateCustomNavigationEventsTest extends TestCase {

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

    public function testUpdatingAndUpdatedEvents()
    {
        $user = $this->createUserAndLoggedIn(array('navigationBuilder.manage' => 1));

        $nav = $this->createDummyNavigation();

        // create event dispatcher mock and inject it to laravel application
        $eventDispatcherMock = $this->getMockBuilder('Illuminate\Events\Dispatcher')
            ->setMethods(array('fire'))
            ->getMock();

        // se expectations
        $eventDispatcherMock->expects($this->exactly(2))
            ->method('fire')
            ->withConsecutive(
                array($this->equalTo('navigationBuilder.updating'), $this->isType('array')),
                array($this->equalTo('navigationBuilder.updated'), $this->isType('array'))
            );

        // inject to laravel "events" IoC alias
        $this->application['events'] = $eventDispatcherMock;

        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\Navigation\Commands\UpdateNavigationCommand',
            array(
                'id' => $nav->id,
                'name' => 'new navigation name',
                'data' => array(
                    'title' => 'home',
                    'class' => 'some-class',
                    'url' => 'www.url.com',
                    'items' => [],
                ),
            )
        );
    }

    protected function createDummyNavigation()
    {
        return Navigation::create(array(
            'name' => 'main navigation',
            'data' => array(
                'title' => 'home',
                'attr' => array(
                    'class' => 'some-class',
                    'id' => 'some-id'
                ),
                'url' => 'http://www.url.com',
                'items' => [],
            ),
        ));
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