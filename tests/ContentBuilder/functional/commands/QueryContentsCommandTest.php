<?php
/**
 * @todo Fix
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/7/2015
 * Time: 12:04 PM
 */

use Darryldecode\Backend\Components\ContentBuilder\Models\Content;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;

class QueryContentsCommandTest extends TestCase {

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

    public function testQueryByType()
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

        // create dummy
        $this->createDummyData($user);

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\QueryContentsCommand',
            array(
                'type' => 'Blog',
            )
        );

        // prove successful
        $this->assertTrue($result->isSuccessful());
        $this->assertEquals('Query contents successful.', $result->getMessage());
        $this->assertEquals(200, $result->getStatusCode(), 'Status code should be ok');
        $this->assertCount(3, $result->getData()->toArray()['data'], 'contents blog should have 3 items');
    }

    public function testQueryWithTermButTermsIsNotAssociatedWithAnyPost()
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

        // create dummy
        $this->createDummyData($user);

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\QueryContentsCommand',
            array(
                'type' => 'Blog',
                'terms' => array(
                    'category' => 'technology'
                )
            )
        );

        // prove successful
        $this->assertTrue($result->isSuccessful());
        $this->assertEquals('Query contents successful.', $result->getMessage());
        $this->assertEquals(200, $result->getStatusCode(), 'Status code should be ok');
        $this->assertCount(0, $result->getData()->toArray()['data']);
    }

    public function testQueryWithTerms()
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

        // create dummy
        $this->createDummyData($user);

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\QueryContentsCommand',
            array(
                'type' => 'Blog',
                'terms' => array(
                    'category' => 'health'
                )
            )
        );

        // prove successful
        $this->assertTrue($result->isSuccessful());
        $this->assertEquals('Query contents successful.', $result->getMessage());
        $this->assertEquals(200, $result->getStatusCode(), 'Status code should be ok');
        $this->assertCount(2, $result->getData()->toArray()['data']);
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
        $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentCommand',
            array(
                'title' => 'Some Title',
                'body' => 'Some Body',
                'slug' => 'some-slug',
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
                'slug' => 'some-slug',
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
                'slug' => 'some-slug-3',
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