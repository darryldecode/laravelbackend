<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/10/2015
 * Time: 8:12 PM
 */

use Darryldecode\Backend\Components\Navigation\Models\Navigation;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;

class CreateNavigationCommandTest extends TestCase {

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
        $user = $this->createUserAndLoggedIn(array());

        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\Navigation\Commands\CreateNavigationCommand',
            array(
                'name' => '',
                'data' => array(),
            )
        );

        $this->assertFalse($result->isSuccessful(), "Command should fail, because user has no permission.");
        $this->assertEquals(403, $result->getStatusCode(), "Status code should be forbidden.");
        $this->assertEquals('Not enough permission.', $result->getMessage());
    }

    public function testNameFieldIsRequired()
    {
        $user = $this->createUserAndLoggedIn(array('navigationBuilder.manage' => 1));

        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\Navigation\Commands\CreateNavigationCommand',
            array(
                'name' => '',
                'data' => array(),
            )
        );

        $this->assertFalse($result->isSuccessful(), "Command should fail, because name field is required.");
        $this->assertEquals(400, $result->getStatusCode(), "Status code should be forbidden.");
        $this->assertEquals('The name field is required.', $result->getMessage());
    }

    public function testDataFieldIsRequired()
    {
        $user = $this->createUserAndLoggedIn(array('navigationBuilder.manage' => 1));

        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\Navigation\Commands\CreateNavigationCommand',
            array(
                'name' => 'SomeNavigationName',
                'data' => '',
            )
        );

        $this->assertFalse($result->isSuccessful(), "Command should fail, because name field is required.");
        $this->assertEquals(400, $result->getStatusCode(), "Status code should be forbidden.");
        $this->assertEquals('The data field is required.', $result->getMessage());
    }

    public function testDataFieldIsArray()
    {
        $user = $this->createUserAndLoggedIn(array('navigationBuilder.manage' => 1));

        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\Navigation\Commands\CreateNavigationCommand',
            array(
                'name' => 'SomeNavigationName',
                'data' => 'not array',
            )
        );

        $this->assertFalse($result->isSuccessful(), "Command should fail, because name field is required.");
        $this->assertEquals(400, $result->getStatusCode(), "Status code should be forbidden.");
        $this->assertEquals('The data must be an array.', $result->getMessage());
    }

    public function testDataFieldIsArrayAndShouldNotBeEmpty()
    {
        $user = $this->createUserAndLoggedIn(array('navigationBuilder.manage' => 1));

        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\Navigation\Commands\CreateNavigationCommand',
            array(
                'name' => 'SomeNavigationName',
                'data' => array(),
            )
        );

        $this->assertFalse($result->isSuccessful(), "Command should fail, because name field is required.");
        $this->assertEquals(400, $result->getStatusCode(), "Status code should be forbidden.");
        $this->assertEquals('The data field is required.', $result->getMessage());
    }

    public function testShouldNowCreateIfAllCheckPointPassed()
    {
        $user = $this->createUserAndLoggedIn(array('navigationBuilder.manage' => 1));

        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\Navigation\Commands\CreateNavigationCommand',
            array(
                'name' => 'SomeNavigationName',
                'data' => array(
                    'title' => 'home',
                    'class' => 'some-class',
                    'url' => 'www.url.com',
                    'items' => [],
                ),
            )
        );

        $this->assertTrue($result->isSuccessful(), "Command should be a success.");
        $this->assertEquals(201, $result->getStatusCode(), "Status code should be created.");
        $this->assertEquals('Navigation successfully created.', $result->getMessage());

        // prove that nav exist
        $this->assertCount(1, Navigation::all()->toArray());
        $this->assertEquals('SomeNavigationName', Navigation::all()->first()->name);
        $this->assertInternalType('array', Navigation::all()->first()->data);
    }

    protected function createUserAndLoggedIn($permissions = array())
    {
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => $permissions
        ));
        $this->application['auth']->loginUsingId($user->id);

        return $user;
    }
}