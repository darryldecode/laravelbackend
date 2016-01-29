<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/28/2015
 * Time: 2:42 PM
 */

use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Mockery as m;

class QueryGroupsCommandTest extends TestCase {

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

    public function testQueryShouldDenyIfUserHasNoPermission()
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

        // dummy request, required first name
        $request = Request::create('','GET',array(
            'name' => null,
            'with' => array(),
        ));

        // begin
        $results = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\User\Commands\QueryGroupsCommand',
            $request
        );

        $this->assertFalse($results->isSuccessful(), 'Transaction should fail as user has no permission');
        $this->assertEquals(403, $results->getStatusCode(), 'Status code should be 403');
        $this->assertEquals('Not enough permission.', $results->getMessage());
    }

    public function testQuery()
    {
        // create user and logged in (the user who will perform the action)
        $user = User::create(array(
            'first_name' => 'dianne',
            'email' => 'dianne@gmail.com',
            'password' => 'pass$dianne',
            'permissions' => array(
                'user.manage' => 1
            )
        ));

        // logged in the user
        $this->application['auth']->loginUsingId($user->id);

        // create dummy groups to be evaluated with
        $this->createGroups();


        // -----------------
        // QUERY ALL
        // -----------------

        // dummy request, required first name
        $request = Request::create('','GET',array(
            'name' => null,
            'with' => array(),
            'paginate' => false
        ));
        $results = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\User\Commands\QueryGroupsCommand',
            $request
        );

        $this->assertTrue($results->isSuccessful(), 'Transaction should be good');
        $this->assertEquals(200, $results->getStatusCode(), 'Status code should be 200');
        $this->assertEquals('Query groups command successful.', $results->getMessage());

        $this->assertCount(3, $results->getData()->toArray(), 'There should be 3 groups');

        // -----------------
        // QUERY BY NAME
        // -----------------

        // dummy request, required first name
        $request = Request::create('','GET',array(
            'name' => 'blogger',
            'with' => array(),
            'paginate' => false
        ));
        $results = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\User\Commands\QueryGroupsCommand',
            $request
        );

        $this->assertTrue($results->isSuccessful(), 'Transaction should be good');
        $this->assertEquals(200, $results->getStatusCode(), 'Status code should be 200');
        $this->assertEquals('Query groups command successful.', $results->getMessage());

        $this->assertCount(1, $results->getData()->toArray(), 'There should be 1 group');
    }

    protected function createGroups()
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
    }
}