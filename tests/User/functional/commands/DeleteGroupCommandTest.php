<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/30/2015
 * Time: 4:41 PM
 */

use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;

class DeleteGroupCommandTest extends TestCase {

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
            'Darryldecode\Backend\Components\User\Commands\DeleteGroupCommand',
            array(
                ''
            )
        );

        $this->assertFalse($result->isSuccessful(), 'Transaction should not be successful.');
        $this->assertEquals(403, $result->getStatusCode(), 'Status code should be forbidden.');
        $this->assertEquals('Not enough permission.', $result->getMessage());
    }

    public function testShouldDeleteGroup()
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

        // prove first that the blog group exist
        // and that user "jane" is in blog group
        $blog = $res['blogger'];
        $jane = $res['user'];
        $this->assertEquals('blogger', Group::find($blog->id)->name);
        $this->assertTrue($jane->inGroup($blog), "Jane should be in blogger group");

        // now lets delete the blogger group
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\DeleteGroupCommand',
            array(
                'id' => $blog->id
            )
        );

        // prove deletion response should be successful
        $this->assertTrue($result->isSuccessful(), 'Transaction should be successful.');
        $this->assertEquals(200, $result->getStatusCode(), 'Status code should be forbidden.');
        $this->assertEquals('Group successfully deleted.', $result->getMessage());

        // blog should not exist now
        $this->assertInternalType('null', Group::find($blog->id));

        // user "jane" should not be in group "blogger" now
        $j = User::find($jane->id);
        $this->assertFalse($j->inGroup($blog), "Jane should not be in blogger group anymore");
    }

    protected function createDummyUserAndGroup()
    {
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
        $user->groups()->attach($blogger);

        return array(
            'blogger' => $blogger,
            'user' => $user,
        );
    }
}