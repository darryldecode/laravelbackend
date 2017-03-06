<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/25/2015
 * Time: 7:41 PM
 */

use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Mockery as m;

class CreatingContentEventsTest extends TestCase {

    protected $application;

    protected $faker;

    /**
     * @var Darryldecode\Backend\Base\Contracts\Bus\Dispatcher
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
        $this->commandDispatcher = $this->application->make('Darryldecode\Backend\Base\Contracts\Bus\Dispatcher');
    }

    public function tearDown()
    {

    }

    protected function createContentType()
    {
        return ContentType::create(array(
            'type' => 'blog',
            'enable_revisions' => true
        ));
    }

    public function testCreatingAndCreatedEvent()
    {
        $loggedInUser = $this->createUserAndLoggedIn();

        $contentType = $this->createContentType();

        // create event dispatcher mock and inject it to laravel application
        $eventDispatcherMock = $this->getMockBuilder('Illuminate\Events\Dispatcher')
            ->setMethods(array('fire'))
            ->getMock();

        // se expectations
        $eventDispatcherMock->expects($this->exactly(2))
            ->method('fire')
            ->withConsecutive(
                array($this->equalTo('blog.creating'), $this->isType('array')),
                array($this->equalTo('blog.created'), $this->isType('array'))
            );

        // inject to laravel "events" IoC alias
        $this->application['events'] = $eventDispatcherMock;

        // begin
        $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentCommand',
            array(
                'title' => 'Some Title',
                'body' => 'Some Body',
                'slug' => 'some-slug',
                'status' => 'published',
                'authorId' => $loggedInUser->id,
                'contentTypeId' => $contentType->id,
                'taxonomies' => array(),
                'metaData' => array('form_1' => array(
                    'meta1' => 'meta value 1',
                    'meta2' => 'meta value 2',
                )),
                'miscData' => array()
            )
        );
    }

    protected function createUserAndLoggedIn()
    {
        $group = Group::create(array(
            'name' => 'blogger',
            'permissions' => array(
                'superuser'=>1
            )
        ));

        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'blog.create' => 0,
                'forum.create' => 0,
            )
        ));

        $user->groups()->attach($group);

        $this->application['auth']->loginUsingId($user->id);

        return $user;
    }
}