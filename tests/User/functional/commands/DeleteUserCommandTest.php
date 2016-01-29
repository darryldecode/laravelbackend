<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/30/2015
 * Time: 3:25 PM
 */

use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;

class DeleteUserCommandTest extends TestCase {

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
        // create user and logged in (the user who will perform the action)
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
            'Darryldecode\Backend\Components\User\Commands\DeleteUserCommand',
            array(
                ''
            )
        );

        $this->assertFalse($result->isSuccessful(), 'Transaction should not be successful.');
        $this->assertEquals(403, $result->getStatusCode(), 'Status code should be forbidden.');
        $this->assertEquals('Not enough permission.', $result->getMessage());
    }

    public function testShouldDeleteUser()
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

        $res = $this->createDummyUserAndGroup();

        // before deleting the user and detaching to any group
        // lets just prove that the the group the user is being associated with
        // contains 1 user

        $artistGroup = Group::with('users')->find($res['artist']->id);
        $this->assertCount(1, $artistGroup->users->toArray());
        $this->assertEquals('jane', $artistGroup->users->first()->first_name);

        $blogger = Group::with('users')->find($res['blogger']->id);
        $this->assertCount(1, $blogger->users->toArray());
        $this->assertEquals('jane', $blogger->users->first()->first_name);

        // create dummy
        $u = $res['user'];

        // begin deletion
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\DeleteUserCommand',
            array(
                'id' => $u->id
            )
        );

        // prove deletion response should be successful
        $this->assertTrue($result->isSuccessful(), 'Transaction should be successful.');
        $this->assertEquals(200, $result->getStatusCode(), 'Status code should be forbidden.');
        $this->assertEquals('User successfully deleted.', $result->getMessage());

        // now this groups should have no user in it now
        $blogger = Group::with('users')->find($res['blogger']->id);
        $artistGroup = Group::with('users')->find($res['artist']->id);
        $this->assertCount(0, $artistGroup->users->toArray());
        $this->assertCount(0, $blogger->users->toArray());

        // user deleted should not exist
        $this->assertInternalType('null', User::find($u->id));
    }

    protected function createDummyUserAndGroup()
    {
        // create user and group
        $artist = Group::create(array(
            'name' => 'artist',
            'permissions' => array(
                'blog.list' => -1,
                'art.create' => 1,
                'art.edit' => 1,
                'art.delete' => -1,
            )
        ));
        $blogger = Group::create(array(
            'name' => 'blogger',
            'permissions' => array(
                'blog.list' => -1,
                'art.create' => 1,
                'art.edit' => 1,
                'art.delete' => -1,
            )
        ));
        $user = User::create(array(
            'first_name' => 'jane',
            'last_name' => 'stark',
            'email' => 'jane@gmail.com',
            'password' => 'pass$jane',
            'permissions' => array(
                'blog.create' => 0,
                'forum.create' => 0,
            )
        ));
        $user->groups()->attach($artist);
        $user->groups()->attach($blogger);

        return array(
            'blogger' => $blogger,
            'artist' => $artist,
            'user' => $user,
        );
    }
}