<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/3/2015
 * Time: 6:32 PM
 */

use Darryldecode\Backend\Components\ContentBuilder\Models\Content;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;

class DeleteContentTypeCommandTest extends TestCase {

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
            'first_name' => 'dianne',
            'email' => 'dianne@gmail.com',
            'password' => 'pass$dianne',
            'permissions' => array(
            )
        ));

        // logged in the user
        $this->application['auth']->loginUsingId($user->id);

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\DeleteContentTypeCommand',
            array(
                'contentTypeId' => '',
            )
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals('Not enough permission.', $result->getMessage());
        $this->assertEquals(403, $result->getStatusCode(), 'Status code should be forbidden');
    }

    public function testShouldNotDeleteContentTypeIfItHasContents()
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

        // create dummy content type
        $blog = ContentType::create(array(
            'type' => 'Blog',
            'enable_revisions' => true
        ));

        $blog->contents()->create(array(
            'title' => 'Some Title',
            'body' => 'Some Body',
            'slug' => 'some-title',
            'status' => Content::CONTENT_PUBLISHED,
            'permission_requirements' => array(),
            'author_id' => $user->id,
            'misc_data' => '',
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\DeleteContentTypeCommand',
            array(
                'contentTypeId' => $blog->id,
            )
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals('Content type has contents. Delete Contents first.', $result->getMessage());
        $this->assertEquals(400, $result->getStatusCode(), 'Status code should be ok');
    }

    public function testShouldDeleteContentType()
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

        // create dummy content type
        $blog = ContentType::create(array(
            'type' => 'Blog',
            'enable_revisions' => true
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\DeleteContentTypeCommand',
            array(
                'contentTypeId' => $blog->id,
            )
        );

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals('Content type successfully deleted.', $result->getMessage());
        $this->assertEquals(200, $result->getStatusCode(), 'Status code should be ok');

        // prove content type has been deleted
        $this->assertInternalType('null', ContentType::find($blog->id));
    }
}