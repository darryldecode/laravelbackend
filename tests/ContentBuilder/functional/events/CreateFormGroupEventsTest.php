<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/3/2015
 * Time: 11:20 AM
 */

use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Mockery as m;

class CreateFormGroupEventsTest extends TestCase {

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
        $user = $this->createUserAndLoggedIn();

        // create event dispatcher mock and inject it to laravel application
        $eventDispatcherMock = $this->getMockBuilder('Illuminate\Events\Dispatcher')
            ->setMethods(array('fire'))
            ->getMock();

        // se expectations
        $eventDispatcherMock->expects($this->exactly(2))
            ->method('fire')
            ->withConsecutive(
                array($this->equalTo('formGroup.creating'), $this->isType('array')),
                array($this->equalTo('formGroup.created'), $this->isType('array'))
            );

        // inject to laravel "events" IoC alias
        $this->application['events'] = $eventDispatcherMock;

        // create a dummy content type
        $contentType = ContentType::create(array(
            'type' => 'Event',
            'enable_revisions' => true,
        ));

        // should require name field
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateFormGroupCommand',
            array(
                'name' => 'Event Organizer',
                'formName' => 'event_organizer',
                'conditions' => array(),
                'fields' => array('some_field'=>'some'),
                'contentTypeId' => $contentType->id,
            )
        );
        $this->assertTrue($result->isSuccessful(), "transaction should be successful.");
        $this->assertEquals(201, $result->getStatusCode(), "status code should be 201");
        $this->assertEquals('Form group successfully created.', $result->getMessage());
    }

    protected function createUserAndLoggedIn()
    {
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'superuser' => 1
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        return $user;
    }
}