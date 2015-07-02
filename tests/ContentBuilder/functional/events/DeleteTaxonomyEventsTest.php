<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/1/2015
 * Time: 7:08 PM
 */

use Darryldecode\Backend\Components\ContentBuilder\Models\Content;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeTaxonomy;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeTaxonomyTerm;
use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Mockery as m;

class DeleteTaxonomyEventsTest extends TestCase {

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

    public function testDeletingAndDeletedEvent()
    {
        $loggedInUser = $this->createUserAndLoggedIn();

        // create event dispatcher mock and inject it to laravel application
        $eventDispatcherMock = $this->getMockBuilder('Illuminate\Events\Dispatcher')
            ->setMethods(array('fire'))
            ->getMock();

        // set expectations
        $eventDispatcherMock->expects($this->exactly(2))
            ->method('fire')
            ->withConsecutive(
                array($this->equalTo('taxonomy.deleting'), $this->isType('array')),
                array($this->equalTo('taxonomy.deleted'), $this->isType('array'))
            );

        // inject to laravel "events" IoC alias
        $this->application['events'] = $eventDispatcherMock;

        // create dummy content type
        $cType = ContentType::create(array(
            'type' => 'Blog',
            'enable_revisions' => true
        ));
        // create taxonomy
        $taxonomy = ContentTypeTaxonomy::create(array(
            'taxonomy' => 'categories',
            'description' => 'some description',
            'parent' => null,
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
            'author_id' => $loggedInUser->id,
            'content_type_id' => $cType->id,
        ));
        $content->terms()->attach($lifestyleTerm);
        $content->terms()->attach($sportsTerm);
        $content->terms()->attach($codingTerm);

        // begin
        $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\DeleteTaxonomyCommand',
            array(
                'taxonomyId' => $taxonomy->id
            )
        );
    }

    protected function createUserAndLoggedIn()
    {
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'superuser' => 1
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        return $user;
    }
}