<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/3/2015
 * Time: 3:31 PM
 */

use Darryldecode\Backend\Components\ContentBuilder\Models\Content;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeFormGroup;
use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;

class UpdateFormGroupCommandTest extends TestCase {

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

        // create a dummy Form group
        $formGroup = ContentTypeFormGroup::create(array(
            'name' => 'Event Location',
            'form_name' => 'event_location',
            'conditions' => array(),
            'fields' => array(),
            'content_type_id' => 2,
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\UpdateFormGroupCommand',
            array()
        );

        $this->assertFalse($result->isSuccessful(), "transaction should fail.");
        $this->assertEquals(403, $result->getStatusCode(), "status code should be forbidden");
        $this->assertEquals('Not enough permission.', $result->getMessage());
    }

    public function testShouldUpdate()
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

        // create a dummy Form group
        $formGroup = ContentTypeFormGroup::create(array(
            'name' => 'Event Location',
            'form_name' => 'event_location',
            'conditions' => array(),
            'fields' => array(),
            'content_type_id' => 2,
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\UpdateFormGroupCommand',
            array(
                'id' => $formGroup->id,
                'name' => 'Event Location Renamed',
                'formName' => 'event_location_renamed',
                'conditions' => array('dummy'=>'changes'),
                'fields' => array('dummy'=>'changes'),
            )
        );

        $this->assertTrue($result->isSuccessful(), "transaction should be successful.");
        $this->assertEquals(200, $result->getStatusCode(), "status code should be ok");
        $this->assertEquals('Form group successfully updated.', $result->getMessage());

        // fields should have the same value as there is no provided values
        $fGroup = ContentTypeFormGroup::find($formGroup->id);
        //$this->assertEquals('Event Location Renamed', $fGroup->name);
        $this->assertEquals('event_location_renamed', $fGroup->form_name);
        $this->assertCount(1, $fGroup->conditions);
        $this->assertCount(1, $fGroup->fields);
        $this->assertEquals(2, $fGroup->content_type_id);
    }

    public function testShouldUpdateWithoutFillingAllFields()
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

        // create a dummy Form group
        $formGroup = ContentTypeFormGroup::create(array(
            'name' => 'Event Location',
            'form_name' => 'event_location',
            'conditions' => array(),
            'fields' => array(),
            'content_type_id' => 2,
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\UpdateFormGroupCommand',
            array(
                'id' => $formGroup->id
            )
        );

        $this->assertTrue($result->isSuccessful(), "transaction should be successful.");
        $this->assertEquals(200, $result->getStatusCode(), "status code should be ok");
        $this->assertEquals('Form group successfully updated.', $result->getMessage());

        // fields should have the same value as there is no provided values
        $fGroup = ContentTypeFormGroup::find($formGroup->id);
        //$this->assertEquals('Event Location', $fGroup->name);
        $this->assertEquals('event_location', $fGroup->form_name);
        $this->assertCount(0, $fGroup->conditions);
        $this->assertCount(0, $fGroup->fields);
        $this->assertEquals(2, $fGroup->content_type_id);
    }
}