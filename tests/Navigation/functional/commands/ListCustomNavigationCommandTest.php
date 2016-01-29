<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/10/2015
 * Time: 9:44 PM
 */

use Darryldecode\Backend\Components\Navigation\Models\Navigation;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;

class ListCustomNavigationCommandTest extends TestCase {

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

    public function testShouldDenyUserIfNoPermission()
    {
        $this->createUserAndLoggedIn(array());

        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\Navigation\Commands\ListCustomNavigationCommand',
            array()
        );

        $this->assertFalse($result->isSuccessful(), "Command should fail because user has no permission.");
        $this->assertEquals(403, $result->getStatusCode(), "Command should be forbidden.");
        $this->assertEquals('Not enough permission.', $result->getMessage());
    }

    public function testShouldReturnCollectionIFQueryParamIsNotPaginated()
    {
        $this->createUserAndLoggedIn(array('navigationBuilder.manage'=>1));

        $this->createDummyData();

        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\Navigation\Commands\ListCustomNavigationCommand',
            array(
                'paginated' => false
            )
        );

        $this->assertTrue($result->isSuccessful(), "Command should fail a success.");
        $this->assertEquals(200, $result->getStatusCode(), "Command should be ok.");
        $this->assertEquals('List custom navigation command successful.', $result->getMessage());

        // prove result is a collection
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $result->getData());

        // should have 3 items
        $this->assertCount(3, $result->getData()->toArray());
    }

    public function testShouldReturnPaginationAwareInstanceIFQueryParamIsPaginated()
    {
        $this->createUserAndLoggedIn(array('navigationBuilder.manage'=>1));

        $this->createDummyData();

        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\Navigation\Commands\ListCustomNavigationCommand',
            array(
            )
        );

        $this->assertTrue($result->isSuccessful(), "Command should fail a success.");
        $this->assertEquals(200, $result->getStatusCode(), "Command should be ok.");
        $this->assertEquals('List custom navigation command successful.', $result->getMessage());

        // prove result is a collection
        $this->assertInstanceOf('Illuminate\Pagination\LengthAwarePaginator', $result->getData());

        // should have 3 items
        $this->assertCount(3, $result->getData()->toArray()['data']);
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

    protected function createDummyData()
    {
        Navigation::create(array(
            'name' => 'SomeNavigationName 1',
            'data' => array(
                array(
                    'title' => 'home',
                    'class' => 'some-class',
                    'url' => 'www.url.com',
                    'items' => [],
                )
            ),
        ));

        Navigation::create(array(
            'name' => 'SomeNavigationName 2',
            'data' => array(
                array(
                    'title' => 'home',
                    'class' => 'some-class',
                    'url' => 'www.url.com',
                    'items' => [],
                )
            ),
        ));

        Navigation::create(array(
            'name' => 'SomeNavigationName 3',
            'data' => array(
                array(
                    'title' => 'home',
                    'class' => 'some-class',
                    'url' => 'www.url.com',
                    'items' => [],
                ),
                array(
                    'title' => 'about',
                    'class' => 'some-class',
                    'url' => 'www.url.com/about',
                    'items' => [],
                )
            ),
        ));
    }
}