<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/25/2015
 * Time: 7:41 PM
 */

use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Mockery as m;

class CreatingContentTypeTaxonomyEventsTest extends TestCase {

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

    public function testCreatingAndCreatedEvent()
    {
        // this is not a super user
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'superuser' => 1
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        // create event dispatcher mock and inject it to laravel application
        $eventDispatcherMock = $this->getMockBuilder('Illuminate\Events\Dispatcher')
            ->setMethods(array('fire'))
            ->getMock();

        // se expectations
        $eventDispatcherMock->expects($this->exactly(2))
            ->method('fire')
            ->withConsecutive(
                array($this->equalTo('contentTypeTaxonomy.creating'), $this->isType('array')),
                array($this->equalTo('contentTypeTaxonomy.created'), $this->isType('array'))
            );

        // inject to laravel "events" IoC alias
        $this->application['events'] = $eventDispatcherMock;

        $contentType = $this->createContentType();

        // required taxonomy field
        $request = Request::create('','GET',array(
            'taxonomy' => 'categories',
            'description' => '',
            'parent' => '',
            'contentTypeId' => $contentType->id,
        ));

        $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentTypeTaxonomyCommand',
            $request
        );
    }

    protected function createContentType()
    {
        return ContentType::create(array(
            'type' => 'blog',
            'enable_revisions' => true
        ));
    }
}