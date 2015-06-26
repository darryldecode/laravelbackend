<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/5/2015
 * Time: 9:11 PM
 */

use Darryldecode\Backend\Components\ContentBuilder\Models\Content;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeFormGroup;
use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;

class DeleteFormGroupCommandTest extends TestCase {

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
            'permissions' => array()
        ));

        $this->application['auth']->loginUsingId($user->id);

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\DeleteFormGroupCommand',
            array(
                'id' => null,
            )
        );

        $this->assertFalse($result->isSuccessful(), "transaction should fail.");
        $this->assertEquals(403, $result->getStatusCode(), "status code should be forbidden");
        $this->assertEquals('Not enough permission.', $result->getMessage());
    }

    public function testShouldDeleteFormGroup()
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

        // create dummy form group
        $fGroup = ContentTypeFormGroup::create(array(
            'name' => 'Event Organizer Details',
            'form_name' => 'event_organizer_details',
            'conditions' => array(),
            'fields' => array(),
            'content_type_id' => 1
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\DeleteFormGroupCommand',
            array(
                'id' => $fGroup->id,
            )
        );

        $this->assertTrue($result->isSuccessful(), "transaction should success.");
        $this->assertEquals(200, $result->getStatusCode(), "status code should be ok");
        $this->assertEquals('Form group successfully deleted.', $result->getMessage());

        // prove the form group do not exist
        $this->assertInternalType('null', ContentTypeFormGroup::find($fGroup->id));
    }
}