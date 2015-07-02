<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/25/2015
 * Time: 3:31 PM
 */

use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeTaxonomy;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeTaxonomyTerm;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;

class CreateTypeTaxonomyTermTest extends TestCase {

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

    public function testShouldDenyIfUserHasNoPermissionOfThatContentTypeTheTermIsFor()
    {
        // create user and logged in
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        // create content type
        $blogContentType = ContentType::create(array(
            'type' => 'blog',
            'enable_revisions' => true
        ));

        // create taxonomy for blog content type
        $taxonomy = $blogContentType->taxonomies()->create(array(
            'taxonomy' => 'categories',
            'description' => '',
        ));

        // begin
        $request = Request::create('','GET',array(
            'term' => 'lifestyle',
            'contentTypeTaxonomyId' => $taxonomy->id,
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateTypeTaxonomyTerm',
            $request
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals(403, $result->getStatusCode());
        $this->assertEquals('Not enough permission.', $result->getMessage());
    }

    public function testValidateRequiredFields()
    {
        // create user and logged in
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'superuser' => 1
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        // create content type
        $blogContentType = ContentType::create(array(
            'type' => 'blog',
            'enable_revisions' => true
        ));

        // create taxonomy for blog content type
        $taxonomy = $blogContentType->taxonomies()->create(array(
            'taxonomy' => 'categories',
            'description' => '',
        ));

        // term field required
        $request = Request::create('','GET',array(
            'term' => '',
            'contentTypeTaxonomyId' => $taxonomy->id,
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateTypeTaxonomyTerm',
            $request
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals('The term field is required.', $result->getMessage());
    }

    public function testShouldPreventDuplicateSlugs()
    {
        // create user and logged in
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'superuser' => 1
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        // create content type
        $blogContentType = ContentType::create(array(
            'type' => 'blog',
            'enable_revisions' => true
        ));

        // create taxonomy for blog content type
        $taxonomy = $blogContentType->taxonomies()->create(array(
            'taxonomy' => 'categories',
            'description' => '',
        ));

        // create first the 1st term
        $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateTypeTaxonomyTerm',
            array(
                'term' => 'lifestyle',
                'slug' => 'lifestyle',
                'contentTypeTaxonomyId' => $taxonomy->id,
            )
        );

        // create second the 2nd term that would cause duplication
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateTypeTaxonomyTerm',
            array(
                'term' => 'lifestyle',
                'slug' => 'lifestyle',
                'contentTypeTaxonomyId' => $taxonomy->id,
            )
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals('The slug has already been taken.', $result->getMessage());
    }

    public function testInvalidContentType()
    {
        // create user and logged in
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'superuser' => 1
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        // create taxonomy for blog content type
        $taxonomy = ContentTypeTaxonomy::create(array(
            'content_type_id' => 2, // content type that does not exist
            'taxonomy' => 'categories',
            'description' => '',
        ));

        // term field required
        $request = Request::create('','GET',array(
            'term' => '',
            'contentTypeTaxonomyId' => $taxonomy->id,
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateTypeTaxonomyTerm',
            $request
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals('Invalid Content Type.', $result->getMessage());
    }

    public function testInvalidContentTaxonomy()
    {
        // create user and logged in
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'superuser' => 1
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        // term field required
        $request = Request::create('','GET',array(
            'term' => '',
            'contentTypeTaxonomyId' => 5, // taxonomy that does not exist
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateTypeTaxonomyTerm',
            $request
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals('Invalid Taxonomy.', $result->getMessage());
    }

    public function testTaxonomyShouldBeCreatedWhenAllCheckPointsPassed()
    {
        // create user and logged in
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'superuser' => 1
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        // create content type
        $blogContentType = ContentType::create(array(
            'type' => 'blog',
            'enable_revisions' => true
        ));

        // create taxonomy for blog content type
        $taxonomy = $blogContentType->taxonomies()->create(array(
            'taxonomy' => 'categories',
            'description' => '',
        ));

        // term field required
        $request = Request::create('','GET',array(
            'term' => 'lifestyle',
            'slug' => 'lifestyle',
            'contentTypeTaxonomyId' => $taxonomy->id,
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateTypeTaxonomyTerm',
            $request
        );

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals(201, $result->getStatusCode());
        $this->assertEquals('Taxonomy term successfully created.', $result->getMessage());

        // verify that term now exist on DB
        $term = ContentTypeTaxonomyTerm::find($result->getData()->id);

        $this->assertEquals($result->getData()->id, $term->id);
        $this->assertEquals('lifestyle', $term->term);
        $this->assertEquals($taxonomy->id, $term->content_type_taxonomy_id);
    }
}