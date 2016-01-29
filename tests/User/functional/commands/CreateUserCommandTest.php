<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/25/2015
 * Time: 10:23 PM
 */

use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;

class CreateUserCommandTest extends TestCase {

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

    public function testShouldDenyIfUserHasNoPermission()
    {
        // create user and logged in
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        // dummy request
        $request = Request::create('','GET',array(
            'firstName' => '',
            'lastName' => '',
            'email' => '',
            'password' => '',
            'permissions' => '',
            'groups' => array(),
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\User\Commands\CreateUserCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful(), 'Transaction should not be successful.');
        $this->assertEquals(403, $result->getStatusCode(), 'Status code should be forbidden');
        $this->assertEquals('Not enough permission.', $result->getMessage());
    }

    public function testShouldRequireFirstNameField()
    {
        $this->createUserAndLoggedIn(array('superuser' => 1));

        // dummy request, required first name
        $request = Request::create('','GET',array(
            'firstName' => '',
            'lastName' => '',
            'email' => '',
            'password' => '',
            'permissions' => '',
            'groups' => array(),
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\User\Commands\CreateUserCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful(), 'Transaction should not be successful.');
        $this->assertEquals(400, $result->getStatusCode(), 'Status code should be 400');
        $this->assertEquals('The first name field is required.', $result->getMessage());
    }

    public function testShouldRequireLastName()
    {
        $this->createUserAndLoggedIn(array('superuser' => 1));

        // dummy request, required email
        $request = Request::create('','GET',array(
            'firstName' => 'John',
            'lastName' => '',
            'email' => '',
            'password' => '',
            'permissions' => '',
            'groups' => array(),
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\User\Commands\CreateUserCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful(), 'Transaction should not be successful.');
        $this->assertEquals(400, $result->getStatusCode(), 'Status code should be 400');
        $this->assertEquals('The last name field is required.', $result->getMessage());
    }

    public function testShouldRequireEmail()
    {
        $this->createUserAndLoggedIn(array('superuser' => 1));

        // dummy request, required email
        $request = Request::create('','GET',array(
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => '',
            'password' => '',
            'permissions' => '',
            'groups' => array(),
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\User\Commands\CreateUserCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful(), 'Transaction should not be successful.');
        $this->assertEquals(400, $result->getStatusCode(), 'Status code should be 400');
        $this->assertEquals('The email field is required.', $result->getMessage());
    }

    public function testShouldRequireEmailToBeUnique()
    {
        $this->createUserAndLoggedIn(array('superuser' => 1));

        // dummy request, required email
        $request = Request::create('','GET',array(
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'darryl@gmail.com', // existing email
            'password' => '',
            'permissions' => '',
            'groups' => array(),
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\User\Commands\CreateUserCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful(), 'Transaction should not be successful.');
        $this->assertEquals(400, $result->getStatusCode(), 'Status code should be 400');
        $this->assertEquals('The email has already been taken.', $result->getMessage());
    }

    public function testShouldCreateUserIfAllCheckPointsArePassed()
    {
        // create user and logged in
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'superuser' => 1
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        // dummy request, required first name
        $request = Request::create('','GET',array(
            'firstName' => 'Darryl',
            'lastName' => 'Fernandez',
            'email' => 'engrdarrylfernandez@gmail.com',
            'password' => 'password',
            'permissions' => array(),
            'groups' => array(),
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\User\Commands\CreateUserCommand',
            $request
        );

        $this->assertTrue($result->isSuccessful(), 'Transaction should be successful.');
        $this->assertEquals(201, $result->getStatusCode(), 'Status code should be created(201)');
        $this->assertEquals('User successfully created.', $result->getMessage());

        // prove it persisted
        $createdUser = User::find($result->getData()->id);

        $this->assertEquals($result->getData()->id, $createdUser->id);
        $this->assertEquals('Darryl', $createdUser->first_name);
        $this->assertEquals('Fernandez', $createdUser->last_name);
        $this->assertEquals('engrdarrylfernandez@gmail.com', $createdUser->email);
    }

    public function testShouldAssociateTheUserToTheGroupIfThereIsAnyProvided()
    {
        // create user and logged in
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'superuser' => 1
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        // create dummy groups
        $blogger = Group::create(array(
            'name' => 'blogger',
            'permissions' => array(
                'blog.list' => 1,
                'blog.create' => 1,
                'blog.edit' => 1,
                'blog.delete' => -1,
            )
        ));

        $artist = Group::create(array(
            'name' => 'artist',
            'permissions' => array(
                'blog.list' => -1,
                'art.create' => 1,
                'art.edit' => 1,
                'art.delete' => -1,
            )
        ));

        $moderator = Group::create(array(
            'name' => 'moderator',
            'permissions' => array(
                'blog.list' => -1,
                'art.create' => 1,
                'art.edit' => 1,
                'art.delete' => -1,
            )
        ));

        // dummy request, required first name
        $request = Request::create('','GET',array(
            'firstName' => 'Darryl',
            'lastName' => 'Fernandez',
            'email' => 'engrdarrylfernandez@gmail.com',
            'password' => 'password',
            'permissions' => array(),
            'groups' => array(
                $blogger->id,
                $artist->id,
            ),
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\User\Commands\CreateUserCommand',
            $request
        );

        $createdUser = User::find($result->getData()->id);

        $this->assertTrue($createdUser->inGroup($blogger) ,'User should be in blogger group');
        $this->assertTrue($createdUser->inGroup($artist), 'User should be in artist group');
        $this->assertFalse($createdUser->inGroup($moderator), 'User should not be in moderator group');
    }

    protected function createUserAndLoggedIn($permissions)
    {
        // create user and logged in
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => $permissions
        ));

        $this->application['auth']->loginUsingId($user->id);
    }
}