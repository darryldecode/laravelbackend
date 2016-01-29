<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/25/2015
 * Time: 12:50 PM
 */

use Darryldecode\Backend\Components\ContentBuilder\Models\Content;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;

class CreateContentCommandTest extends TestCase {

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

    public function testRequiredFields()
    {
        $this->createUserAndLoggedIn();

        $contentType = $this->createContentType();

        // required title
        $request = Request::create('','GET',array(
            'title' => '',
            'body' => '',
            'slug' => '',
            'status' => '',
            'authorId' => '',
            'contentTypeId' => $contentType->id,
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals('The title field is required.', $result->getMessage());

        // required body
        $request = Request::create('','GET',array(
            'title' => 'Some Title',
            'body' => '',
            'slug' => '',
            'status' => '',
            'authorId' => '',
            'contentTypeId' => $contentType->id,
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals('The body field is required.', $result->getMessage());

        // required slug field
        $request = Request::create('','GET',array(
            'title' => 'Some Title',
            'body' => 'Some Body',
            'slug' => '',
            'status' => '',
            'authorId' => '',
            'contentTypeId' => $contentType->id,
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals('The slug field is required.', $result->getMessage());

        // required author field
        $request = Request::create('','GET',array(
            'title' => 'Some Title',
            'body' => 'Some Body',
            'slug' => 'some-title',
            'status' => '',
            'authorId' => '',
            'contentTypeId' => $contentType->id,
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals('The author id field is required.', $result->getMessage());

        // required author numeric field
        $request = Request::create('','GET',array(
            'title' => 'Some Title',
            'body' => 'Some Body',
            'slug' => 'some-title',
            'status' => '',
            'authorId' => 'not numeric',
            'contentTypeId' => $contentType->id,
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals('The author id must be a number.', $result->getMessage());
    }

    public function testCreateContentShouldBePersistedWhenAllValidationPassed()
    {
        $loggedInUser = $this->createUserAndLoggedIn();

        $contentType = $this->createContentType();

        // dummy request
        $request = Request::create('','GET',array(
            'title' => 'Some Title',
            'body' => 'Some Body',
            'slug' => 'some-slug',
            'status' => 'published',
            'authorId' => $loggedInUser->id,
            'contentTypeId' => $contentType->id,
            'taxonomies' => array(),
            'metaData' => array('form_1' => array(
                'meta1' => 'meta value 1',
                'meta2' => 'meta value 2',
            )),
            'miscData' => array()
        ));

        // begin
        $result = $this->commandDispatcher->dispatchFrom(
            Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentCommand::class,
            $request
        );

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals(201, $result->getStatusCode());
        $this->assertEquals('Content successfully created.', $result->getMessage());

        // prove it exist
        $content = Content::with('metaData')->find($result->getData()->id);

        $this->assertEquals('Some Title', $content->title);
        $this->assertEquals('Some Body', $content->body);
        $this->assertEquals('some-slug', $content->slug);
        //$this->assertEquals('published', $content->status); this passed but annoying for IDE mapper giving error
        $this->assertEquals($loggedInUser->id, $content->author_id);
        $this->assertEquals($contentType->id, $content->content_type_id);

        $contentMeta = Content::parseMetaData($content->metaData->toArray());

        $this->assertCount(2, $contentMeta['form_1']);
        $this->assertArrayHasKey('meta1', $contentMeta['form_1']);
        $this->assertArrayHasKey('meta2', $contentMeta['form_1']);
    }

    protected function createContentType()
    {
        return ContentType::create(array(
            'type' => 'blog',
            'enable_revisions' => true
        ));
    }

    protected function createUserAndLoggedIn()
    {
        $group = Group::create(array(
            'name' => 'blogger',
            'permissions' => array(
                'superuser' => 1,
            )
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