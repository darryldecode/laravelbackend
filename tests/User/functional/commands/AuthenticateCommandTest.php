<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 7/30/2015
 * Time: 7:09 PM
 */

use Darryldecode\Backend\Components\User\Models\Throttle;
use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Mockery as m;

class AuthenticateCommandTest extends TestCase {

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

    public function testShouldFailWithNoMatchingRecords()
    {
        // create user and logged in (the user who will perform the action)
        User::create(array(
            'first_name' => 'darryl',
            'email' => 'admin@gmail.com',
            'password' => 'admin',
            'permissions' => array(
            )
        ));

        // dummy request with none existing credentials
        $request = Request::create('','POST',array(
            'email' => 'some@email.com',
            'password' => 'password',
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\Auth\Commands\AuthenticateCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful(), 'Transaction should not be successful.');
        $this->assertEquals(401, $result->getStatusCode(), 'Status code should be unauthorized.');
        $this->assertEquals('These credentials do not match our records.', $result->getMessage());
    }

    public function testShouldFailWithLoginThrottling()
    {
        // create user and logged in (the user who will perform the action)
        User::create(array(
            'first_name' => 'darryl',
            'email' => 'admin@gmail.com',
            'password' => 'admin',
            'permissions' => array(
            )
        ));

        // dummy request with none existing credentials
        $request = Request::create('','POST',array(
            'email' => 'admin@gmail.com',
            'password' => 'somewrongpassword',
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\Auth\Commands\AuthenticateCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful(), 'Transaction should not be successful.');
        $this->assertEquals(401, $result->getStatusCode(), 'Status code should be unauthorized.');
        $this->assertEquals('These credentials do not match our records. Login attempt remaining: 4', $result->getMessage());
    }

    public function testShouldFailIfUserIsBanned()
    {
        // create user and logged in (the user who will perform the action)
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'admin@gmail.com',
            'password' => 'admin',
            'permissions' => array(
            )
        ));

        // create throttle banned entry
        Throttle::create(array(
            'user_id' => $user->id,
            'banned' => true
        ));

        // dummy request with none existing credentials
        $request = Request::create('','POST',array(
            'email' => 'admin@gmail.com',
            'password' => 'admin',
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\Auth\Commands\AuthenticateCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful(), 'Transaction should not be successful.');
        $this->assertEquals(401, $result->getStatusCode(), 'Status code should be unauthorized.');
        $this->assertEquals('This account is currently banned.', $result->getMessage());
    }

    public function testShouldFailIfUserIsSuspended()
    {
        // create user and logged in (the user who will perform the action)
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'admin@gmail.com',
            'password' => 'admin',
            'permissions' => array(
            )
        ));

        // create throttle banned entry
        Throttle::create(array(
            'user_id' => $user->id,
            'suspended' => true,
            'suspended_at' => Carbon\Carbon::now(),
        ));

        // dummy request with none existing credentials
        $request = Request::create('','POST',array(
            'email' => 'admin@gmail.com',
            'password' => 'admin',
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\Auth\Commands\AuthenticateCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful(), 'Transaction should not be successful.');
        $this->assertEquals(401, $result->getStatusCode(), 'Status code should be unauthorized.');
        $this->assertEquals('This account is currently suspended. You can login after 15 minutes.', $result->getMessage());
    }

    public function testShouldFailWithLoginThrottlingAndShouldBeBannedAfter5Attempts()
    {
        // create user and logged in (the user who will perform the action)
        User::create(array(
            'first_name' => 'darryl',
            'email' => 'admin@gmail.com',
            'password' => 'admin',
            'permissions' => array(
            )
        ));

        // dummy request with none existing credentials
        $request = Request::create('','POST',array(
            'email' => 'admin@gmail.com',
            'password' => 'somewrongpassword',
        ));

        // begin first attempt
        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\Auth\Commands\AuthenticateCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful(), 'Transaction should not be successful.');
        $this->assertEquals(401, $result->getStatusCode(), 'Status code should be unauthorized.');
        $this->assertEquals('These credentials do not match our records. Login attempt remaining: 4', $result->getMessage());

        // begin second attempt
        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\Auth\Commands\AuthenticateCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful(), 'Transaction should not be successful.');
        $this->assertEquals(401, $result->getStatusCode(), 'Status code should be unauthorized.');
        $this->assertEquals('These credentials do not match our records. Login attempt remaining: 3', $result->getMessage());

        // begin third attempt
        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\Auth\Commands\AuthenticateCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful(), 'Transaction should not be successful.');
        $this->assertEquals(401, $result->getStatusCode(), 'Status code should be unauthorized.');
        $this->assertEquals('These credentials do not match our records. Login attempt remaining: 2', $result->getMessage());

        // begin fourth attempt
        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\Auth\Commands\AuthenticateCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful(), 'Transaction should not be successful.');
        $this->assertEquals(401, $result->getStatusCode(), 'Status code should be unauthorized.');
        $this->assertEquals('These credentials do not match our records. Login attempt remaining: 1', $result->getMessage());

        // begin fifth attempt
        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\Auth\Commands\AuthenticateCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful(), 'Transaction should not be successful.');
        $this->assertEquals(401, $result->getStatusCode(), 'Status code should be unauthorized.');
        $this->assertEquals('These credentials do not match our records. Login attempt remaining: 0', $result->getMessage());

        // user now be on suspended state
        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\Auth\Commands\AuthenticateCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful(), 'Transaction should not be successful.');
        $this->assertEquals(401, $result->getStatusCode(), 'Status code should be unauthorized.');
        $this->assertEquals('This account is currently suspended. You can login after 15 minutes.', $result->getMessage());
    }
}