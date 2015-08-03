<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/26/2015
 * Time: 3:33 PM
 */

use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Mockery as m;

class CreateGroupCommandTest extends TestCase {

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

    public function testCreateGroupShouldDenyIfUserPerformingActionIsNotSuperUser()
    {
        // create user and logged in (the user who will perform the action)
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        // dummy request
        $request = Request::create('','POST',array(
            'name' => '',
            'permissions' => array(),
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\User\Commands\CreateGroupCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful(), 'Transaction should not be successful.');
        $this->assertEquals(403, $result->getStatusCode(), 'Status code should be forbidden.');
        $this->assertEquals('Not enough permission.', $result->getMessage());
    }

    public function testRequiredNameField()
    {
        // create user and logged in (the user who will perform the action)
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'superuser' => 1
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        // dummy request | name field should be required
        $request = Request::create('','POST',array(
            'name' => '',
            'permissions' => array(),
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\User\Commands\CreateGroupCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful(), 'Transaction should not be successful.');
        $this->assertEquals(400, $result->getStatusCode(), 'Status code should be 400.');
        $this->assertEquals('The name field is required.', $result->getMessage());
    }

    public function testPermissionsFieldShouldBeAnArray()
    {
        // create user and logged in (the user who will perform the action)
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'superuser' => 1
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        $request = Request::create('','POST',array(
            'name' => 'moderator',
            'permissions' => 'Not an array',
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\User\Commands\CreateGroupCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful(), 'Transaction should not be successful.');
        $this->assertEquals(400, $result->getStatusCode(), 'Status code should be 400.');
        $this->assertEquals('The permissions must be an array.', $result->getMessage());
    }

    public function testCreateShouldCreateGroupWhenAllCheckPointsPassed()
    {
        // create user and logged in (the user who will perform the action)
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'superuser' => 1
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        // dummy request
        $request = Request::create('','POST',array(
            'name' => 'moderator',
            'permissions' => array(
                'forum.create' => 1,
                'forum.delete' => -1,
            ),
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\User\Commands\CreateGroupCommand',
            $request
        );

        $this->assertTrue($result->isSuccessful(), 'Transaction should be successful.');
        $this->assertEquals(201, $result->getStatusCode(), 'Status code should be created.');
        $this->assertEquals('Group successfully created.', $result->getMessage());

        // prove persisted
        $group = Group::find($result->getData()->id);

        //$this->assertEquals('moderator',$group->name); this pass but just comment it because IDE skwaks!
        $this->assertInternalType('array',$group->permissions);
        $this->assertCount(2,$group->permissions);
        $this->assertArrayHasKey('forum.create',$group->permissions);
        $this->assertArrayHasKey('forum.delete',$group->permissions);
    }

    public function testShouldCreateGroupEvenWithoutPermissionsYet()
    {
        // create user and logged in (the user who will perform the action)
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'superuser' => 1
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        $request = Request::create('','POST',array(
            'name' => 'moderator',
            'permissions' => array(),
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\User\Commands\CreateGroupCommand',
            $request
        );

        $this->assertTrue($result->isSuccessful(), 'Transaction should be successful.');
        $this->assertEquals(201, $result->getStatusCode(), 'Status code should be 400.');
        $this->assertEquals('Group successfully created.', $result->getMessage());
    }
}