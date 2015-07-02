<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/30/2015
 * Time: 8:54 PM
 */

use Darryldecode\Backend\Components\ContentBuilder\Models\Content;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;

class QueryContentTypeTest extends TestCase {

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

    public function testQueryWithNoNameParameterShouldReturnResults()
    {
        // create user and logged in (the user who will perform the action)
        $user = User::create(array(
            'first_name' => 'dianne',
            'email' => 'dianne@gmail.com',
            'password' => 'pass$dianne',
            'permissions' => array(
                'superuser' => 1
            )
        ));

        // logged in the user
        $this->application['auth']->loginUsingId($user->id);

        // create dummies
        $this->createDummyContentTypes();

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\QueryContentTypeCommand',
            array(
                'type' => null,
            )
        );

        // prove successful
        $this->assertTrue($result->isSuccessful());
        $this->assertEquals('Query content types successful.', $result->getMessage());
        $this->assertEquals(200, $result->getStatusCode(), 'Status code should be ok');

        // prove contents
        $this->assertCount(3, $result->getData()->toArray(), "Should have 3 content types");
    }

    public function testQueryWithNameParameterShouldReturnResults()
    {
        // create user and logged in (the user who will perform the action)
        $user = User::create(array(
            'first_name' => 'dianne',
            'email' => 'dianne@gmail.com',
            'password' => 'pass$dianne',
            'permissions' => array(
                'superuser' => 1
            )
        ));

        // logged in the user
        $this->application['auth']->loginUsingId($user->id);

        // create dummies
        $this->createDummyContentTypes();

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\QueryContentTypeCommand',
            array(
                'type' => 'blog',
            )
        );

        // prove successful
        $this->assertTrue($result->isSuccessful());
        $this->assertEquals('Query content types successful.', $result->getMessage());
        $this->assertEquals(200, $result->getStatusCode(), 'Status code should be ok');

        // prove contents
        $this->assertCount(1, $result->getData()->toArray(), "Should have 3 content types");
        $this->assertEquals('Blog', $result->getData()->first()->type);
    }

    protected function createDummyContentTypes()
    {
        ContentType::create(array(
            'type' => 'Blog',
            'enable_revisions' => true
        ));
        ContentType::create(array(
            'type' => 'Events',
            'enable_revisions' => true
        ));
        ContentType::create(array(
            'type' => 'News',
            'enable_revisions' => true
        ));
    }
}