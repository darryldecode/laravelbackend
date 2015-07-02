<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/3/2015
 * Time: 12:05 PM
 */

use Darryldecode\Backend\Components\ContentBuilder\Models\Content;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeFormGroup;
use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;

class QueryFormGroupCommandTest extends TestCase {

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

    public function testQueryShouldDenyUserHasNoPermission()
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
            'Darryldecode\Backend\Components\ContentBuilder\Commands\QueryFormGroupCommand',
            array(
                'paginated' => true,
                'perPage' => 6,
                'contentTypeId' => $contentType->id,
            )
        );

        $this->assertFalse($result->isSuccessful(), "transaction should fail.");
        $this->assertEquals(403, $result->getStatusCode(), "status code should be forbidden");
        $this->assertEquals('Not enough permission.', $result->getMessage());
    }

    public function testQueryShouldFailIfContentTypeIsInvalidOrDoesNotExist()
    {
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\QueryFormGroupCommand',
            array(
                'paginated' => true,
                'perPage' => 6,
                'contentTypeId' => 3, // content type that does not exist
            )
        );

        $this->assertFalse($result->isSuccessful(), "transaction should fail.");
        $this->assertEquals(400, $result->getStatusCode(), "status code should be forbidden");
        $this->assertEquals('Content Type not found.', $result->getMessage());
    }

    public function testQueryShouldReturnPaginationObjectIfQuestedWithPagination()
    {
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'Event.manage' => 1
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        // create a dummy content type
        $contentType = ContentType::create(array(
            'type' => 'Event',
            'enable_revisions' => true,
        ));
        // create dummy Form Groups
        ContentTypeFormGroup::create(array(
            'name' => 'Event Organizer',
            'form_name' => 'event_organizer',
            'conditions' => array(),
            'fields' => array(),
            'content_type_id' => $contentType->id,
        ));
        ContentTypeFormGroup::create(array(
            'name' => 'Event Location',
            'form_name' => 'event_location',
            'conditions' => array(),
            'fields' => array(),
            'content_type_id' => $contentType->id,
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\QueryFormGroupCommand',
            array(
                'paginated' => true,
                'perPage' => 6,
                'contentTypeId' => $contentType->id,
            )
        );

        $this->assertTrue($result->isSuccessful(), "transaction should be successful.");
        $this->assertEquals(200, $result->getStatusCode(), "status code should be ok");
        $this->assertEquals('Query form groups command successful.', $result->getMessage());

        // prove paginated instance
        $this->assertInstanceOf('Illuminate\Pagination\LengthAwarePaginator', $result->getData());

        // prove has two items
        $this->assertCount(2, $result->getData()->toArray()['data']);
    }

    public function testQueryShouldReturnCollectionObjectIfQuestedWithNotPaginated()
    {
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'Event.manage' => 1
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        // create a dummy content type
        $contentType = ContentType::create(array(
            'type' => 'Event',
            'enable_revisions' => true,
        ));
        // create dummy Form Groups
        ContentTypeFormGroup::create(array(
            'name' => 'Event Organizer',
            'form_name' => 'event_organizer',
            'conditions' => array(),
            'fields' => array(),
            'content_type_id' => $contentType->id,
        ));
        ContentTypeFormGroup::create(array(
            'name' => 'Event Location',
            'form_name' => 'event_location',
            'conditions' => array(),
            'fields' => array(),
            'content_type_id' => $contentType->id,
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\QueryFormGroupCommand',
            array(
                'paginated' => false,
                'perPage' => 6,
                'contentTypeId' => $contentType->id,
            )
        );

        $this->assertTrue($result->isSuccessful(), "transaction should be successful.");
        $this->assertEquals(200, $result->getStatusCode(), "status code should be ok");
        $this->assertEquals('Query form groups command successful.', $result->getMessage());

        // prove paginated instance
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $result->getData());

        // prove has two items
        $this->assertCount(2, $result->getData()->toArray());
    }
}