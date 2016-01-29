<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/3/2015
 * Time: 8:13 AM
 */

use Darryldecode\Backend\Components\ContentBuilder\Models\Content;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;

class CreateFormGroupCommandTest extends TestCase {

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

    public function testShouldBeDeniedWhenUserHasNoPermission()
    {
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        // create a dummy content type
        $contentType = ContentType::create(array(
            'type' => 'Event',
            'enable_revisions' => true,
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateFormGroupCommand',
            array(
                'name' => 'Event Organizer',
                'formName' => 'event_organizer',
                'conditions' => array(),
                'fields' => array(),
                'contentTypeId' => $contentType->id,
            )
        );

        $this->assertFalse($result->isSuccessful(), "transaction should fail.");
        $this->assertEquals(403, $result->getStatusCode(), "status code should be forbidden");
        $this->assertEquals('Not enough permission.', $result->getMessage());
    }

    public function testRequiredFields()
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

        // create a dummy content type
        $contentType = ContentType::create(array(
            'type' => 'Event',
            'enable_revisions' => true,
        ));

        // should require name field
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateFormGroupCommand',
            array(
                'name' => '',
                'formName' => 'event_organizer',
                'conditions' => array(),
                'fields' => array(),
                'contentTypeId' => $contentType->id,
            )
        );
        $this->assertFalse($result->isSuccessful(), "transaction should fail.");
        $this->assertEquals(400, $result->getStatusCode(), "status code should be 400");
        $this->assertEquals('The name field is required.', $result->getMessage());

        // should require form name field
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateFormGroupCommand',
            array(
                'name' => 'Event Organizer',
                'formName' => '',
                'conditions' => array(),
                'fields' => array(),
                'contentTypeId' => $contentType->id,
            )
        );
        $this->assertFalse($result->isSuccessful(), "transaction should fail.");
        $this->assertEquals(400, $result->getStatusCode(), "status code should be 400");
        $this->assertEquals('The form name field is required.', $result->getMessage());

        // should require fields
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateFormGroupCommand',
            array(
                'name' => 'Event Organizer',
                'formName' => 'event_organizer',
                'conditions' => array(),
                'fields' => null,
                'contentTypeId' => $contentType->id,
            )
        );
        $this->assertFalse($result->isSuccessful(), "transaction should fail.");
        $this->assertEquals(400, $result->getStatusCode(), "status code should be 400");
        $this->assertEquals('The fields field is required.', $result->getMessage());

    }

    public function testShouldFailIfContentTypeDoesNotExist()
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

        // should require name field
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateFormGroupCommand',
            array(
                'name' => 'Event Organizer',
                'formName' => 'event_organizer',
                'conditions' => array(),
                'fields' => array('some_field'=>'some'),
                'contentTypeId' => 2, // content type that does not exist
            )
        );
        $this->assertFalse($result->isSuccessful(), "transaction should fail.");
        $this->assertEquals(400, $result->getStatusCode(), "status code should be 400");
        $this->assertEquals('Content Type Not Found.', $result->getMessage());
    }

    public function testShouldCreateFormGroupIfAllCheckPointPassed()
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
}