<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 7/1/2015
 * Time: 6:30 PM
 */

use Darryldecode\Backend\Components\ContentBuilder\Models\Content;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentRevisions;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;

class UpdateContentCommandTest extends TestCase {

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

    public function testShouldUpdate()
    {
        $user = $this->createUserAndLoggedIn(array('superuser'=>1));

        $content = $this->createDummyData($user);

        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\UpdateContentCommand',
            array(
                'id' => $content->id,
                'title' => 'Some Title 2',
                'body' => 'Some Body',
                'slug' => 'some-slug',
                'status' => 'published',
                'contentTypeId' => $content->type->id
            )
        );

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals('Content successfully updated.', $result->getMessage());

        // prove content title was changed
        $this->assertEquals('Some Title 2',$result->getData()->title);
    }

    public function testShouldUpdateWithRevision()
    {
        $user = $this->createUserAndLoggedIn(array('superuser'=>1));

        $content = $this->createDummyData($user);

        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\UpdateContentCommand',
            array(
                'id' => $content->id,
                'title' => 'Some Title 2',
                'body' => 'Some Body 2',
                'slug' => 'some-slug',
                'status' => 'published',
                'contentTypeId' => $content->type->id
            )
        );

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals('Content successfully updated.', $result->getMessage());

        // prove content title was changed
        $this->assertEquals('Some Title 2',$result->getData()->title);

        // prove we have now 1 revision entry
        $revision =  ContentRevisions::all();
        $this->assertCount(1, $revision->toArray());

        // check our revision content
        $this->assertEquals('Some Body',$revision->first()->old_content);
        $this->assertEquals('Some Body 2',$revision->first()->new_content);
    }

    public function testShouldUpdateWithoutRevisionIfContentTypeRevisionIsDisabled()
    {
        $user = $this->createUserAndLoggedIn(array('superuser'=>1));

        $content = $this->createDummyData($user, false);

        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\UpdateContentCommand',
            array(
                'id' => $content->id,
                'title' => 'Some Title 2',
                'body' => 'Some Body 2',
                'slug' => 'some-slug',
                'status' => 'published',
                'contentTypeId' => $content->type->id
            )
        );

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals('Content successfully updated.', $result->getMessage());

        // prove content title was changed
        $this->assertEquals('Some Title 2',$result->getData()->title);

        // there should be no revisions
        $revision =  ContentRevisions::all();
        $this->assertCount(0, $revision->toArray());
    }

    public function testShouldNotCreateRevisionIfContentBodyDidNotChanged()
    {
        $user = $this->createUserAndLoggedIn(array('superuser'=>1));

        $content = $this->createDummyData($user);

        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\UpdateContentCommand',
            array(
                'id' => $content->id,
                'title' => 'Some Title 2',
                'body' => 'Some Body',
                'slug' => 'some-slug',
                'status' => 'published',
                'contentTypeId' => $content->type->id
            )
        );

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals('Content successfully updated.', $result->getMessage());

        // prove content title was changed
        $this->assertEquals('Some Title 2',$result->getData()->title);

        // there should be no revisions because content body is the same
        $revision =  ContentRevisions::all();
        $this->assertCount(0, $revision->toArray());
    }

    protected function createDummyData($user, $enableRevisions = true)
    {
        $blogContentType = ContentType::create(array(
            'type' => 'Blog',
            'enable_revisions' => $enableRevisions
        ));

        return Content::create(array(
            'title' => 'Some Title',
            'body' => 'Some Body',
            'slug' => 'some-slug',
            'status' => 'published',
            'author_id' => $user->id,
            'content_type_id' => $blogContentType->id,
            'taxonomies' => array('luzon'),
            'misc_data' => array()
        ));
    }

    protected function createUserAndLoggedIn($permissions)
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