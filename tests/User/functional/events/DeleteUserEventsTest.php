<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/30/2015
 * Time: 3:40 PM
 */

use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Mockery as m;

class DeleteUserEventsTest extends TestCase {

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

    public function testShouldFireDeletingAndDeletedEvents()
    {
        // create user and logged in
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'superuser' => 1
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
                array($this->equalTo('user.deleting'), $this->isType('array')),
                array($this->equalTo('user.deleted'), $this->isType('array'))
            );

        // inject to laravel "events" IoC alias
        $this->application['events'] = $eventDispatcherMock;

        // create dummy
        $res = $this->createDummyUserAndGroup();
        $u = $res['user'];

        // begin deletion
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\DeleteUserCommand',
            array(
                'id' => $u->id
            )
        );
    }

    protected function createDummyUserAndGroup()
    {
        // create user and group
        $blogger = Group::create(array(
            'name' => 'blogger',
            'permissions' => array(
                'blog.list' => -1,
                'art.create' => 1,
                'art.edit' => 1,
                'art.delete' => -1,
            )
        ));
        $user = User::create(array(
            'first_name' => 'jane',
            'last_name' => 'stark',
            'email' => 'jane@gmail.com',
            'password' => 'pass$jane',
            'permissions' => array(
                'blog.create' => 0,
                'forum.create' => 0,
            )
        ));
        $user->groups()->attach($blogger);

        return array(
            'blogger' => $blogger,
            'user' => $user,
        );
    }
}