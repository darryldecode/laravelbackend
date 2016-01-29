<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/3/2015
 * Time: 6:37 PM
 */


use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Mockery as m;

class DeletingContentTypeEventsTest extends TestCase {

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

    public function testDeletingAndDeletedEventShouldBeFiredWhenContentTypeIsSuccessfullyDeleted()
    {
        // create user and logged in (the user who will perform the action)
        $user = User::create(array(
            'first_name' => 'dianne',
            'email' => 'dianne@gmail.com',
            'password' => 'pass$dianne',
            'permissions' => array(
                'superuser' => 1
            )
        ));

        // logged in the user
        $this->application['auth']->loginUsingId($user->id);

        // create event dispatcher mock and inject it to laravel application
        $eventDispatcherMock = $this->getMockBuilder('Illuminate\Events\Dispatcher')
            ->setMethods(array('fire'))
            ->getMock();

        // se expectations
        $eventDispatcherMock->expects($this->exactly(2))
            ->method('fire')
            ->withConsecutive(
                array($this->equalTo('contentType.deleting'), $this->isType('array')),
                array($this->equalTo('contentType.deleted'), $this->isType('array'))
            );

        // inject to laravel "events" IoC alias
        $this->application['events'] = $eventDispatcherMock;

        // create dummy content type
        $blog = ContentType::create(array(
            'type' => 'Blog',
            'enable_revisions' => true
        ));

        // begin
        $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\DeleteContentTypeCommand',
            array(
                'contentTypeId' => $blog->id,
            )
        );
    }
}