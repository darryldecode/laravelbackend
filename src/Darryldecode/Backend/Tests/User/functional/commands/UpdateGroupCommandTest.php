<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/29/2015
 * Time: 8:58 AM
 */

use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Mockery as m;

class UpdateGroupCommandTest extends TestCase {

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

    public function testShouldBeDeniedIfUserHasNoPermission()
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
            'id' => '',
            'name' => '',
            'permissions' => array(),
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\UpdateGroupCommand',
            array(
                'id' => $request->get('id', null),
                'name' => $request->get('name', null),
                'permissions' => $request->get('permissions', array()),
            )
        );

        $this->assertFalse($result->isSuccessful(), 'Transaction should not be successful.');
        $this->assertEquals(403, $result->getStatusCode(), 'Status code should be 403.');
        $this->assertEquals('Not enough permission.', $result->getMessage());
    }

    public function testShouldUpdate()
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

        // create the group that we will be updating
        $blogger = Group::create(array(
            'name' => 'blogger',
            'permissions' => array(
                'blog.list' => 1,
                'blog.create' => 1,
                'blog.edit' => 1,
                'blog.delete' => -1,
            )
        ));

        // dummy request
        // we will update the blogger group that it will not be able to create a blog now
        $request = Request::create('','POST',array(
            'id' => $blogger->id,
            'name' => 'blogger-renamed',
            'permissions' => array(
                'blog.list' => 1,
                'blog.create' => -1,
                'blog.edit' => 1,
                'blog.delete' => -1,
            ),
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\UpdateGroupCommand',
            array(
                'id' => $request->get('id', null),
                'name' => $request->get('name', null),
                'permissions' => $request->get('permissions', array()),
            )
        );

        $this->assertTrue($result->isSuccessful(), 'Transaction should be successful.');
        $this->assertEquals(200, $result->getStatusCode(), 'Status code should be 200.');
        $this->assertEquals('Group successfully updated.', $result->getMessage());

        // now prove it has been updated
        $updatedGroup = Group::find($result->getData()->id);

        //$this->assertEquals('blogger-renamed', $updatedGroup->name);
        $this->assertArrayHasKey('blog.list', $updatedGroup->getPermissionsAttribute(), 'Group permissions should have key blog.list');
        $this->assertArrayHasKey('blog.create', $updatedGroup->getPermissionsAttribute(), 'Group permissions should have key blog.create');
        $this->assertArrayHasKey('blog.edit', $updatedGroup->getPermissionsAttribute(), 'Group permissions should have key blog.edit');
        $this->assertArrayHasKey('blog.delete', $updatedGroup->getPermissionsAttribute(), 'Group permissions should have key blog.delete');
        $this->assertEquals(1, $updatedGroup->getPermissionsAttribute()['blog.list'], 'Permission blog.list should be allow');
        $this->assertEquals(-1, $updatedGroup->getPermissionsAttribute()['blog.create'], 'Permission blog.create should be deny');
        $this->assertEquals(1, $updatedGroup->getPermissionsAttribute()['blog.edit'], 'Permission blog.edit should be allow');
        $this->assertEquals(-1, $updatedGroup->getPermissionsAttribute()['blog.delete'], 'Permission blog.delete should be deny');
    }
}