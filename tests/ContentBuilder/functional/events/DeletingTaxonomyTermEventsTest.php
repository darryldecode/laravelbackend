<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 6/23/2015
 * Time: 7:05 PM
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

class DeletingTaxonomyTermEventsTest extends TestCase {

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

        // se expectations
        $eventDispatcherMock->expects($this->exactly(2))
            ->method('fire')
            ->withConsecutive(
                array($this->equalTo('taxonomyTerm.deleting'), $this->isType('array')),
                array($this->equalTo('taxonomyTerm.deleted'), $this->isType('array'))
            );

        // inject to laravel "events" IoC alias
        $this->application['events'] = $eventDispatcherMock;

        // lets generate dummy data so we have something to work on
        $this->createDummyData();

        // let's prove first we now have a term
        $this->assertNotEmpty(ContentTypeTaxonomyTerm::all()->toArray());
        $this->assertEquals('lifestyle',ContentTypeTaxonomyTerm::all()->first()->term);

        // now let's delete the term
        $request = Request::create('','GET',array(
            'taxonomyId' => 1, // obviously we have only 1 data to this so we just assumed id is 1
            'termId' => 1, // obviously we have only 1 data to this so we just assumed id is 1
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\DeleteTaxonomyTermCommand',
            $request
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

    protected function createDummyData()
    {
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

        // create the terms
        $taxonomy->terms()->create(array(
            'term' => 'lifestyle',
            'slug' => 'lifestyle',
        ));
    }
}