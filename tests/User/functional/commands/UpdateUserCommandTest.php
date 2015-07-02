<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/29/2015
 * Time: 7:12 PM
 */

use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Mockery as m;

class UpdateUserCommandTest extends TestCase {

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

    public function testShouldDenyIfUserNotAuthorized()
    {
        $this->createAndLoginUser(array());

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\UpdateUserCommand',
            array()
        );

        $this->assertFalse($result->isSuccessful(), 'Transaction should not be successful.');
        $this->assertEquals(403, $result->getStatusCode(), 'Status code should be 403.');
        $this->assertEquals('Not enough permission.', $result->getMessage());
    }

    public function testShouldOnlyUpdateFirstName()
    {
        $this->createAndLoginUser(array('superuser'=>1));

        $user = $this->createDummyUserAndGroup();

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\UpdateUserCommand',
            array(
                'id' => $user->id,
                'firstName' => 'Claire',
                'lastName' => null,
                'email' => null,
                'password' => null,
                'permissions' => null,
                'groups' => null,
            )
        );

        $updatedUser = $result->getData();

        $this->assertTrue($result->isSuccessful(), 'Transaction should be successful.');
        $this->assertEquals(200, $result->getStatusCode(), 'Status code should be 200.');
        $this->assertEquals('User successfully updated.', $result->getMessage());

        // prove first name was updated
        $this->assertEquals('Claire', $updatedUser->first_name);
        $this->assertEquals('stark', $updatedUser->last_name); // should not be changed
        $this->assertEquals('jane@gmail.com', $updatedUser->email); // should not be changed

        // should be still in group
        $this->assertTrue($updatedUser->inGroup('artist'), "Should still in group artist");

        // should have following permissions
        $this->assertTrue($updatedUser->hasPermission('art.create'));
        $this->assertTrue($updatedUser->hasPermission('art.edit'));

        // should not have permissions
        $this->assertFalse($updatedUser->hasPermission('blog.create'));
        $this->assertFalse($updatedUser->hasPermission('blog.list'));
        $this->assertFalse($updatedUser->hasPermission('art.delete'));
        $this->assertFalse($updatedUser->hasPermission('forum.create'));
    }

    public function testShouldOnlyUpdateLastName()
    {
        $this->createAndLoginUser(array('superuser'=>1));

        $user = $this->createDummyUserAndGroup();

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\UpdateUserCommand',
            array(
                'id' => $user->id,
                'firstName' => null,
                'lastName' => 'aaron',
                'email' => null,
                'password' => null,
                'permissions' => null,
                'groups' => null,
            )
        );

        $updatedUser = $result->getData();

        $this->assertTrue($result->isSuccessful(), 'Transaction should be successful.');
        $this->assertEquals(200, $result->getStatusCode(), 'Status code should be 200.');
        $this->assertEquals('User successfully updated.', $result->getMessage());

        // prove first name was updated
        $this->assertEquals('jane', $updatedUser->first_name); // should not be changed
        $this->assertEquals('aaron', $updatedUser->last_name); // should be changed
        $this->assertEquals('jane@gmail.com', $updatedUser->email); // should not be changed

        // should be still in group
        $this->assertTrue($updatedUser->inGroup('artist'), "Should still in group artist");

        // should have following permissions
        $this->assertTrue($updatedUser->hasPermission('art.create'));
        $this->assertTrue($updatedUser->hasPermission('art.edit'));

        // should not have permissions
        $this->assertFalse($updatedUser->hasPermission('blog.create'));
        $this->assertFalse($updatedUser->hasPermission('blog.list'));
        $this->assertFalse($updatedUser->hasPermission('art.delete'));
        $this->assertFalse($updatedUser->hasPermission('forum.create'));
    }

    public function testShouldOnlyUpdateEmail()
    {
        $this->createAndLoginUser(array('superuser'=>1));

        $user = $this->createDummyUserAndGroup();

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\UpdateUserCommand',
            array(
                'id' => $user->id,
                'firstName' => null,
                'lastName' => null,
                'email' => 'new@gmail.com',
                'password' => null,
                'permissions' => null,
                'groups' => null,
            )
        );

        $updatedUser = $result->getData();

        $this->assertTrue($result->isSuccessful(), 'Transaction should be successful.');
        $this->assertEquals(200, $result->getStatusCode(), 'Status code should be 200.');
        $this->assertEquals('User successfully updated.', $result->getMessage());

        // prove first name was updated
        $this->assertEquals('jane', $updatedUser->first_name); // should not be changed
        $this->assertEquals('stark', $updatedUser->last_name); // should not be changed
        $this->assertEquals('new@gmail.com', $updatedUser->email); // should be changed

        // should be still in group
        $this->assertTrue($updatedUser->inGroup('artist'), "Should still in group artist");

        // should have following permissions
        $this->assertTrue($updatedUser->hasPermission('art.create'));
        $this->assertTrue($updatedUser->hasPermission('art.edit'));

        // should not have permissions
        $this->assertFalse($updatedUser->hasPermission('blog.create'));
        $this->assertFalse($updatedUser->hasPermission('blog.list'));
        $this->assertFalse($updatedUser->hasPermission('art.delete'));
        $this->assertFalse($updatedUser->hasPermission('forum.create'));
    }

    public function testShouldNotUpdateEmailIfExist()
    {
        $this->createAndLoginUser(array('superuser'=>1));

        $user = $this->createDummyUserAndGroup();

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\UpdateUserCommand',
            array(
                'id' => $user->id,
                'firstName' => null,
                'lastName' => null,
                'email' => 'darryl@gmail.com',
                'password' => null,
                'permissions' => null,
                'groups' => null,
            )
        );

        $this->assertFalse($result->isSuccessful(), 'Transaction should not be successful.');
        $this->assertEquals(400, $result->getStatusCode(), 'Status code should be 400.');
        $this->assertEquals('Email already in used.', $result->getMessage());
    }

    public function testShouldNotUpdatePasswordIfNoProvided()
    {
        $this->createAndLoginUser(array('superuser'=>1));

        $user = $this->createDummyUserAndGroup();

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\UpdateUserCommand',
            array(
                'id' => $user->id,
                'firstName' => null,
                'lastName' => null,
                'email' => null,
                'password' => null,
                'permissions' => null,
                'groups' => null,
            )
        );

        $updatedUser = $result->getData();

        $this->assertTrue($result->isSuccessful(), 'Transaction should be successful.');
        $this->assertEquals(200, $result->getStatusCode(), 'Status code should be 200.');
        $this->assertEquals('User successfully updated.', $result->getMessage());

        // prove first name was updated
        $this->assertEquals('jane', $updatedUser->first_name); // should not be changed
        $this->assertEquals('stark', $updatedUser->last_name); // should not be changed
        $this->assertEquals('jane@gmail.com', $updatedUser->email); // should be changed

        // should be still in group
        $this->assertTrue($updatedUser->inGroup('artist'), "Should still in group artist");

        // should have following permissions
        $this->assertTrue($updatedUser->hasPermission('art.create'));
        $this->assertTrue($updatedUser->hasPermission('art.edit'));

        // should not have permissions
        $this->assertFalse($updatedUser->hasPermission('blog.create'));
        $this->assertFalse($updatedUser->hasPermission('blog.list'));
        $this->assertFalse($updatedUser->hasPermission('art.delete'));
        $this->assertFalse($updatedUser->hasPermission('forum.create'));

        // pass should be the same
        $this->assertTrue($this->application['hash']->check('pass$jane', $updatedUser->password), "password should not be changed");
    }

    public function testShouldUpdatePasswordIfProvided()
    {
        $this->createAndLoginUser(array('superuser'=>1));

        $user = $this->createDummyUserAndGroup();

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\UpdateUserCommand',
            array(
                'id' => $user->id,
                'firstName' => null,
                'lastName' => null,
                'email' => null,
                'password' => 'newpassword',
                'permissions' => null,
                'groups' => null,
            )
        );

        $updatedUser = $result->getData();

        $this->assertTrue($result->isSuccessful(), 'Transaction should be successful.');
        $this->assertEquals(200, $result->getStatusCode(), 'Status code should be 200.');
        $this->assertEquals('User successfully updated.', $result->getMessage());

        // prove first name was updated
        $this->assertEquals('jane', $updatedUser->first_name); // should not be changed
        $this->assertEquals('stark', $updatedUser->last_name); // should not be changed
        $this->assertEquals('jane@gmail.com', $updatedUser->email); // should be changed

        // should be still in group
        $this->assertTrue($updatedUser->inGroup('artist'), "Should still in group artist");

        // should have following permissions
        $this->assertTrue($updatedUser->hasPermission('art.create'));
        $this->assertTrue($updatedUser->hasPermission('art.edit'));

        // should not have permissions
        $this->assertFalse($updatedUser->hasPermission('blog.create'));
        $this->assertFalse($updatedUser->hasPermission('blog.list'));
        $this->assertFalse($updatedUser->hasPermission('art.delete'));
        $this->assertFalse($updatedUser->hasPermission('forum.create'));

        // pass should be the same
        $this->assertTrue($this->application['hash']->check('newpassword', $updatedUser->password), "password should be changed");
    }

    public function testUpdateGroup()
    {
        $this->createAndLoginUser(array('superuser'=>1));

        $user = $this->createDummyUserAndGroup();

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\UpdateUserCommand',
            array(
                'id' => $user->id,
                'firstName' => null,
                'lastName' => null,
                'email' => null,
                'password' => null,
                'permissions' => null,
                'groups' => array(),
            )
        );

        $updatedUser = $result->getData();

        $this->assertTrue($result->isSuccessful(), 'Transaction should be successful.');
        $this->assertEquals(200, $result->getStatusCode(), 'Status code should be 200.');
        $this->assertEquals('User successfully updated.', $result->getMessage());

        // prove first name was updated
        $this->assertEquals('jane', $updatedUser->first_name); // should not be changed
        $this->assertEquals('stark', $updatedUser->last_name); // should not be changed
        $this->assertEquals('jane@gmail.com', $updatedUser->email); // should be changed

        // should be still in group
        $this->assertFalse($updatedUser->inGroup('artist'), "Should still be removed in group artist");

        // should have no following permissions now
        $this->assertFalse($updatedUser->hasPermission('art.create'));
        $this->assertFalse($updatedUser->hasPermission('art.edit'));

        // should not have permissions
        $this->assertFalse($updatedUser->hasPermission('blog.create'));
        $this->assertFalse($updatedUser->hasPermission('blog.list'));
        $this->assertFalse($updatedUser->hasPermission('art.delete'));
        $this->assertFalse($updatedUser->hasPermission('forum.create'));

        // pass should be the same
        $this->assertTrue($this->application['hash']->check('pass$jane', $updatedUser->password), "password should be changed");
    }

    protected function createAndLoginUser($permissions)
    {
        // create user and logged in (the user who will perform the action)
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => $permissions
        ));

        $this->application['auth']->loginUsingId($user->id);
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

        return $user;
    }
}