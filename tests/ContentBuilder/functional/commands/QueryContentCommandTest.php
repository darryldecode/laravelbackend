<?php

use Darryldecode\Backend\Components\ContentBuilder\Models\Content;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;

class QueryContentCommandTest extends TestCase {

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

        $this->createDummyData($this->createUserAndLoggedIn());
    }

    public function tearDown()
    {

    }

    public function testQueryByID()
    {
        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\QueryContentCommand',
            array(
                'id' => 1,
            )
        );

        // prove successful
        $this->assertTrue($result->isSuccessful());
        $this->assertEquals('Query content successful.', $result->getMessage());
        $this->assertEquals(200, $result->getStatusCode(), 'Status code should be ok');

        $this->assertEquals('Some Title',$result->getData()->toArray()['title']);
    }

    public function testQueryBySlug()
    {
        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\QueryContentCommand',
            array(
                'slug' => 'some-entry-2',
            )
        );

        // prove successful
        $this->assertTrue($result->isSuccessful());
        $this->assertEquals('Query content successful.', $result->getMessage());
        $this->assertEquals(200, $result->getStatusCode(), 'Status code should be ok');

        $this->assertEquals('Some Entry 2',$result->getData()->toArray()['title']);
    }

    public function testQueryByTitleSearch()
    {
        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\QueryContentCommand',
            array(
                'title' => 'Entry 3',
            )
        );

        // prove successful
        $this->assertTrue($result->isSuccessful());
        $this->assertEquals('Query content successful.', $result->getMessage());
        $this->assertEquals(200, $result->getStatusCode(), 'Status code should be ok');

        $this->assertEquals('Some Entry 3',$result->getData()->toArray()['title']);
    }

    protected function createUserAndLoggedIn()
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

        return $user;
    }


    protected function createDummyData($user)
    {
        // create content type
        $ctype = ContentType::create(array(
            'type' => 'Blog',
            'enable_revisions' => true
        ));

        // create content type taxonomy
        $taxonomy = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentTypeTaxonomyCommand',
            array(
                'taxonomy' => 'category',
                'description' => 'the blog post category',
                'contentTypeId' => $ctype->id,
            )
        );
        $taxonomy = $taxonomy->getData()->toArray();

        // create taxonomy terms
        $technologyTerm = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateTypeTaxonomyTerm',
            array(
                'term' => 'technology',
                'slug' => 'technology',
                'contentTypeTaxonomyId' => $taxonomy['id'],
            )
        );
        $technologyTerm = $technologyTerm->getData()->toArray();

        $healthTerm = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateTypeTaxonomyTerm',
            array(
                'term' => 'health',
                'slug' => 'health',
                'contentTypeTaxonomyId' => $taxonomy['id'],
            )
        );
        $healthTerm = $healthTerm->getData()->toArray();

        $programmingTerm = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateTypeTaxonomyTerm',
            array(
                'term' => 'programming',
                'slug' => 'programming',
                'contentTypeTaxonomyId' => $taxonomy['id'],
            )
        );
        $programmingTerm = $programmingTerm->getData()->toArray();

        // create blog post dummy contents
        // this will have ID of 1, we will use this on test query by ID
        $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentCommand',
            array(
                'title' => 'Some Title',
                'body' => 'Some Body',
                'slug' => 'some-title',
                'status' => 'published',
                'authorId' => $user->id,
                'contentTypeId' => $ctype->id,
                'taxonomies' => array($healthTerm['id']=>true),
                'metaData' => array('form_1' => array(
                    'meta1' => 'meta value 1',
                    'meta2' => 'meta value 2',
                )),
                'miscData' => array()
            )
        );

        $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentCommand',
            array(
                'title' => 'Some Entry 2',
                'body' => 'Some entry entry',
                'slug' => 'some-entry-2',
                'status' => 'published',
                'authorId' => $user->id,
                'contentTypeId' => $ctype->id,
                'taxonomies' => array($programmingTerm['id']=>true, $healthTerm['id']=>true),
                'metaData' => array('form_1' => array(
                    'meta1' => 'meta value 1',
                    'meta2' => 'meta value 2',
                )),
                'miscData' => array()
            )
        );

        $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentCommand',
            array(
                'title' => 'Some Entry 3',
                'body' => 'Some entry entry 3',
                'slug' => 'some-entry-3',
                'status' => 'published',
                'authorId' => $user->id,
                'contentTypeId' => $ctype->id,
                'taxonomies' => array(),
                'metaData' => array('form_1' => array(
                    'meta1' => 'meta value 1',
                    'meta2' => 'meta value 2',
                )),
                'miscData' => array()
            )
        );
    }
}