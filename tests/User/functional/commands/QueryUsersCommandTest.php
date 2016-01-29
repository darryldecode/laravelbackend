<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/27/2015
 * Time: 5:13 PM
 */

use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Mockery as m;

class QueryUsersCommandTest extends TestCase {

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

    public function testShouldDenyQueryIfUserIsNotAuthorized()
    {
        // create user and logged in (the user who will perform the action)
        $user = User::create(array(
            'first_name' => 'dianne',
            'email' => 'dianne@gmail.com',
            'password' => 'pass$dianne',
            'permissions' => array(
            )
        ));

        // logged in the user
        $this->application['auth']->loginUsingId($user->id);

        $this->createUsers();

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\QueryUsersCommand',
            array(
                'firstName' => '',
                'lastName' => '',
                'email' => '',
                'groupId' => '',
                'with' => '',
                'orderBy' => '',
                'orderSort' => '',
                'paginated' => '',
                'perPage' => '',
            )
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals('Not enough permission.', $result->getMessage());
        $this->assertEquals(403, $result->getStatusCode(), 'Status code should be forbidden');
    }

    public function testQueryParameters()
    {
        // create user and logged in (the user who will perform the action)
        $user = User::create(array(
            'first_name' => 'dianne',
            'last_name' => 'monte',
            'email' => 'dianne@gmail.com',
            'password' => 'pass$dianne',
            'permissions' => array(
                'user.manage' => 1
            )
        ));

        // logged in the user
        $this->application['auth']->loginUsingId($user->id);

        $this->createUsers();

        // ----------------------
        // begin query all
        // ----------------------
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\QueryUsersCommand',
            array(
                'firstName' => null,
                'lastName' => null,
                'email' => null,
                'groupId' => null,
                'with' => array(),
                'orderBy' => 'created_at',
                'orderSort' => 'ASC',
                'paginated' => true,
                'perPage' => 15,
            )
        );

        $this->assertTrue($result->isSuccessful());
        $this->assertCount(5, $result->getData()->toArray()['data'], 'Total users should be 5 including dianne the logged in user');

        // ----------------------
        // begin query by first name like
        // ----------------------
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\QueryUsersCommand',
            array(
                'firstName' => 'dar',
                'lastName' => null,
                'email' => null,
                'groupId' => null,
                'with' => array(),
                'orderBy' => 'created_at',
                'orderSort' => 'ASC',
                'paginated' => true,
                'perPage' => 15,
            )
        );

        $this->assertTrue($result->isSuccessful());
        $this->assertCount(1, $result->getData()->toArray()['data'], 'should only find 1 item');
        $this->assertEquals('darryl', $result->getData()->first()->first_name);

        // ----------------------
        // begin query by last name like
        // ----------------------
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\QueryUsersCommand',
            array(
                'firstName' => '',
                'lastName' => 'monte',
                'email' => null,
                'groupId' => null,
                'with' => array(),
                'orderBy' => 'created_at',
                'orderSort' => 'ASC',
                'paginated' => true,
                'perPage' => 15,
            )
        );

        $this->assertTrue($result->isSuccessful());
        $this->assertCount(1, $result->getData()->toArray()['data'], 'should only find 1 item');
        $this->assertEquals('dianne', $result->getData()->first()->first_name, 'should only find 1 item');

        // ----------------------
        // begin query by email
        // ----------------------
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\QueryUsersCommand',
            array(
                'firstName' => null,
                'lastName' => null,
                'email' => 'jane@gmail.com',
                'groupId' => null,
                'with' => array(),
                'orderBy' => 'created_at',
                'orderSort' => 'ASC',
                'paginated' => true,
                'perPage' => 15,
            )
        );

        $this->assertTrue($result->isSuccessful());
        $this->assertCount(1, $result->getData()->toArray()['data'], 'should only find 1 item');
        $this->assertEquals('jane', $result->getData()->first()->first_name, 'should only find 1 item');
    }

    public function testQueryWithGroupId()
    {
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

        // create user and logged in (the user who will perform the action)
        $user = User::create(array(
            'first_name' => 'dianne',
            'last_name' => 'monte',
            'email' => 'dianne@gmail.com',
            'password' => 'pass$dianne',
            'permissions' => array(
                'user.manage' => 1
            )
        ));

        $user->groups()->attach($blogger);

        // logged in the user
        $this->application['auth']->loginUsingId($user->id);

        $this->createUsers();

        // ----------------------
        // begin query by group
        // ----------------------
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\QueryUsersCommand',
            array(
                'firstName' => null,
                'lastName' => null,
                'email' => null,
                'groupId' => $blogger->id,
                'with' => array(),
                'orderBy' => 'created_at',
                'orderSort' => 'ASC',
                'paginated' => true,
                'perPage' => 15,
            )
        );

        $this->assertTrue($result->isSuccessful());
        $this->assertCount(1, $result->getData()->toArray()['data'], 'should only find 1 item');
        $this->assertEquals('dianne', $result->getData()->first()->first_name, 'should only find 1 item');
    }

    protected function createUsers()
    {
        User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'superuser' => 1
            )
        ));
        User::create(array(
            'first_name' => 'noemi',
            'email' => 'noemi@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'blogger' => 1
            )
        ));
        User::create(array(
            'first_name' => 'jane',
            'email' => 'jane@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'moderator' => 1
            )
        ));
        User::create(array(
            'first_name' => 'janelyn',
            'email' => 'janelyn@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'moderator' => 1
            )
        ));
    }
}