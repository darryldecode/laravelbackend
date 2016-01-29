<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/1/2015
 * Time: 2:15 PM
 */

use Darryldecode\Backend\Components\ContentBuilder\Models\Content;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeTaxonomy;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeTaxonomyTerm;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;

class DeleteTaxonomyCommandTest extends TestCase {

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

    public function testShouldDenyIfUserHasNoPermission()
    {
        // this is not a super user
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\DeleteTaxonomyCommand',
            array(
                'taxonomyId' => null
            )
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals(403, $result->getStatusCode());
        $this->assertEquals('Not enough permission.', $result->getMessage());
    }

    public function testShouldDeleteTaxonomy()
    {
        // this is not a super user
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'superuser' => 1
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        // create dummy content type
        $cType = ContentType::create(array(
            'type' => 'Blog',
            'enable_revisions' => true
        ));
        // create taxonomy and give it also a child
        $taxonomy = ContentTypeTaxonomy::create(array(
            'taxonomy' => 'categories',
            'description' => 'some description',
            'content_type_id' => $cType->id,
        ));
        $childTaxonomy = ContentTypeTaxonomy::create(array(
            'taxonomy' => 'categories-child',
            'description' => 'some description',
            'content_type_id' => $cType->id,
        ));
        // create now terms
        $lifestyleTerm = ContentTypeTaxonomyTerm::create(array(
            'term' => 'lifestyle',
            'slug' => 'lifestyle',
            'content_type_taxonomy_id' => $taxonomy->id,
        ));
        $sportsTerm = ContentTypeTaxonomyTerm::create(array(
            'term' => 'sports',
            'slug' => 'sports',
            'content_type_taxonomy_id' => $taxonomy->id,
        ));
        $codingTerm = ContentTypeTaxonomyTerm::create(array(
            'term' => 'coding',
            'slug' => 'coding',
            'content_type_taxonomy_id' => $taxonomy->id,
        ));
        // create a Blog post
        $content = Content::create(array(
            'title' => 'Blog Post Title',
            'body' => 'Some blog body',
            'slug' => 'blog-post-title',
            'status' => Content::CONTENT_PUBLISHED,
            'author_id' => $user->id,
            'content_type_id' => $cType->id,
        ));
        $content->terms()->attach($lifestyleTerm);
        $content->terms()->attach($sportsTerm);
        $content->terms()->attach($codingTerm);

        // lets verify first that the post was indeed to have those 3 terms
        $c = Content::with('terms')->find($content->id);
        $this->assertCount(3, $c->terms->toArray(), "The Blog post should have 3 terms");
        $this->assertCount(3, ContentTypeTaxonomyTerm::all()->toArray(), "Ther should be 3 terms");

        // now lets start to delete the taxonomy
        // it should also delete the terms and the BLog post should no longer have those terms
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\DeleteTaxonomyCommand',
            array(
                'taxonomyId' => $taxonomy->id
            )
        );

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals('Taxonomy successfully deleted.', $result->getMessage());

        // the blog post should not contain any terms anymore
        $c = Content::with('terms')->find($content->id);
        $this->assertCount(0, $c->terms->toArray(), "The Blog Post should not have any terms anymore");

        // taxonomy should be delete
        $this->assertInternalType('null', ContentTypeTaxonomy::find($taxonomy->id));
    }
}