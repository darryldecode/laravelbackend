<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/8/2015
 * Time: 2:22 PM
 */

use Darryldecode\Backend\Components\ContentBuilder\Models\Content;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;

class DeleteContentCommandTest extends TestCase {

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

    public function testShouldDenyIfUserHasNoPermissionForSpecificContentType()
    {
        $user = $this->createUserAndLoggedIn(array('Blog.manage'=>1));

        $createdContent = $this->createDummyData($user);

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\DeleteContentCommand',
            array(
                'id' => $createdContent->id
            )
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals(403, $result->getStatusCode());
        $this->assertEquals('Not enough permission.', $result->getMessage());
    }

    public function testShouldDeleteIfAllCheckPointsPassed()
    {
        $user = $this->createUserAndLoggedIn(array('Blog.manage'=>1,'Blog.delete'=>1));

        $createdContent = $this->createDummyData($user);

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\DeleteContentCommand',
            array(
                'id' => $createdContent->id
            )
        );

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals('Blog content successfully deleted.', $result->getMessage());

        // there should be no contents now
        $this->assertCount(0, Content::all()->toArray(), 'There should be no contents now');
    }

    protected function createDummyData($user)
    {
        $blogContentType = ContentType::create(array(
            'type' => 'Blog',
            'enable_revisions' => true
        ));

        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentCommand',
            array(
                'title' => 'Some Title',
                'body' => 'Some Body',
                'slug' => 'some-slug',
                'status' => 'published',
                'authorId' => $user->id,
                'contentTypeId' => $blogContentType->id,
                'taxonomies' => array('luzon'),
                'metaData' => array('form_1' => array(
                    'meta1' => 'meta value 1',
                    'meta2' => 'meta value 2',
                )),
                'miscData' => array()
            )
        );

        return $result->getData();
    }

    protected function createUserAndLoggedIn($permissions)
    {
        $group = Group::create(array(
            'name' => 'blogger',
            'permissions' => $permissions
        ));

        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'blog.create' => 0,
                'forum.create' => 0,
            )
        ));

        $user->groups()->attach($group);

        $this->application['auth']->loginUsingId($user->id);

        return $user;
    }
}