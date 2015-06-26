<?php

use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeTaxonomy;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeTaxonomyTerm;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;

class DeleteTaxonomyTermCommandTest extends TestCase {

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

    public function testShouldDenyIfTaxonomyDoesNotExist()
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

        // now let's delete the term
        $request = Request::create('','GET',array(
            'taxonomyId' => 3, // this does not exist -> evaluate
            'termId' => 1, // this does not exist
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\DeleteTaxonomyTermCommand',
            $request
        );

        // we should not be able to delete it because we don't have a permission for a blog to manage
        $this->assertFalse($result->isSuccessful());
        $this->assertEquals('Invalid Taxonomy.', $result->getMessage());
        $this->assertEquals(400, $result->getStatusCode());
    }

    public function testShouldDenyIfTermDoesNotExist()
    {
        // create user and logged in
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'blog.manage' => 1
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

        // add term to the taxonomy
        $taxonomy->terms()->create(array(
            'term' => 'coding',
            'slug' => 'coding'
        ));

        // now let's delete the term
        $request = Request::create('','GET',array(
            'taxonomyId' => $taxonomy->id, // this does not exist
            'termId' => 3, // this does not exist -> evaluate
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\DeleteTaxonomyTermCommand',
            $request
        );

        // we should not be able to delete it because we don't have a permission for a blog to manage
        $this->assertFalse($result->isSuccessful());
        $this->assertEquals('Invalid Taxonomy Term.', $result->getMessage());
        $this->assertEquals(400, $result->getStatusCode());

        $this->application['auth']->loginUsingId($user->id);
    }

    public function testShouldDenyUserFromDeletingATermIfTheUserHasNoPermissionOnTheContentTypeTheTermBelongs()
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

        // add term to the taxonomy
        $taxonomy->terms()->create(array(
            'term' => 'coding',
            'slug' => 'coding'
        ));

        // let's prove first we have now 1 taxonomy term for categories which is coding
        $this->assertCount(1, ContentTypeTaxonomyTerm::all()->toArray());
        $this->assertEquals('coding',ContentTypeTaxonomyTerm::all()->first()->term);

        // now let's delete the term
        $request = Request::create('','GET',array(
            'taxonomyId' => $blogContentType->id,
            'termId' => $taxonomy->id,
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\DeleteTaxonomyTermCommand',
            $request
        );

        // we should not be able to delete it because we don't have a permission for a blog to manage
        $this->assertFalse($result->isSuccessful());
        $this->assertEquals('Not enough permission.', $result->getMessage());
        $this->assertEquals(403, $result->getStatusCode());
    }

    public function testShouldDeleteTerm()
    {
        // create user and logged in
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'blog.manage' => 1
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

        // add term to the taxonomy
        $taxonomy->terms()->create(array(
            'term' => 'coding',
            'slug' => 'coding'
        ));

        // let's prove first we have now 1 taxonomy term for categories which is coding
        $this->assertCount(1, ContentTypeTaxonomyTerm::all()->toArray());
        $this->assertEquals('coding',ContentTypeTaxonomyTerm::all()->first()->term);

        // now let's delete the term
        $request = Request::create('','GET',array(
            'taxonomyId' => $blogContentType->id,
            'termId' => $taxonomy->id,
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\DeleteTaxonomyTermCommand',
            $request
        );

        // we should not be able to delete it because we don't have a permission for a blog to manage
        $this->assertTrue($result->isSuccessful());
        $this->assertEquals('Taxonomy Term successfully deleted.', $result->getMessage());
        $this->assertEquals(200, $result->getStatusCode());

        // let's prove that there is no term now on our records
        $this->assertEmpty(ContentTypeTaxonomyTerm::all()->toArray());
    }
}