<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 7/1/2015
 * Time: 7:26 PM
 */

use Darryldecode\Backend\Components\ContentBuilder\Models\Content;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Mockery as m;

class UpdatingContentEventsTest extends TestCase {

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
        $loggedInUser = $this->createUserAndLoggedIn();

        $content = $this->createDummyData($loggedInUser);

        // create event dispatcher mock and inject it to laravel application
        $eventDispatcherMock = $this->getMockBuilder('Illuminate\Events\Dispatcher')
            ->setMethods(array('fire'))
            ->getMock();

        // se expectations
        $eventDispatcherMock->expects($this->exactly(2))
            ->method('fire')
            ->withConsecutive(
                array($this->equalTo('blog.updating'), $this->isType('array')),
                array($this->equalTo('blog.updated'), $this->isType('array'))
            );

        // inject to laravel "events" IoC alias
        $this->application['events'] = $eventDispatcherMock;

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\UpdateContentCommand',
            array(
                'id' => $content->id,
                'title' => 'Some Title 2',
                'body' => 'Some Body',
                'slug' => 'some-slug',
                'status' => 'published',
                'contentTypeId' => $content->type->id
            )
        );
    }

    protected function createDummyData($user)
    {
        $blogContentType = ContentType::create(array(
            'type' => 'blog',
            'enable_revisions' => true
        ));

        return Content::create(array(
            'title' => 'Some Title',
            'body' => 'Some Body',
            'slug' => 'some-slug',
            'status' => 'published',
            'author_id' => $user->id,
            'content_type_id' => $blogContentType->id,
            'taxonomies' => array('luzon'),
            'misc_data' => array()
        ));
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