<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 6/24/2015
 * Time: 6:10 PM
 */

use Darryldecode\Backend\Components\Navigation\Models\Navigation;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;

class UpdateCustomNavigationCommandTest extends TestCase {

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

    public function testValidationNavigationNameFieldRequired()
    {
        $this->createUserAndLoggedIn(array('navigationBuilder.manage' => 1));

        $this->createDummyNavigation();

        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\Navigation\Commands\UpdateNavigationCommand',
            array(
                'name' => '',
                'data' => array(
                    'title' => 'home',
                    'class' => 'some-class',
                    'url' => 'www.url.com',
                    'items' => [],
                ),
            )
        );

        $this->assertFalse($result->isSuccessful(),"result should fail because name field is empty");
        $this->assertEquals('The name field is required.',$result->getMessage());
    }

    public function testValidationNavigationDataFieldRequired()
    {
        $this->createUserAndLoggedIn(array('navigationBuilder.manage' => 1));

        $this->createDummyNavigation();

        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\Navigation\Commands\UpdateNavigationCommand',
            array(
                'name' => 'main navigation',
                'data' => null,
            )
        );

        $this->assertFalse($result->isSuccessful(),"result should fail because data field is empty");
        $this->assertEquals('The data field is required.',$result->getMessage());
    }

    public function testShouldUpdateNavigation()
    {
        $this->createUserAndLoggedIn(array('navigationBuilder.manage' => 1));

        $nav = $this->createDummyNavigation();

        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\Navigation\Commands\UpdateNavigationCommand',
            array(
                'id' => $nav->id,
                'name' => 'main navigation updated',
                'data' => array(
                    array(
                        'title' => 'home',
                        'attr' => array(
                            'class' => 'some-class',
                            'id' => 'some-id'
                        ),
                        'url' => 'http://www.url.com',
                        'items' => []
                    ),
                    array(
                        'title' => 'about',
                        'attr' => array(
                            'class' => 'some-class',
                            'id' => 'some-id'
                        ),
                        'url' => 'http://www.url.com/about',
                        'items' => []
                    ),
                ),
            )
        );

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals('Navigation successfully updated.',$result->getMessage());

        // prove the data has been changed/updated
        $updatedNav = Navigation::all()->first();
        $this->assertEquals('main navigation updated',$updatedNav->name);
        $this->assertCount(2,$updatedNav->data);
    }

    protected function createDummyNavigation()
    {
        return Navigation::create(array(
            'name' => 'main navigation',
            'data' => array(
                'title' => 'home',
                'attr' => array(
                    'class' => 'some-class',
                    'id' => 'some-id'
                ),
                'url' => 'http://www.url.com',
                'items' => [],
            ),
        ));
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