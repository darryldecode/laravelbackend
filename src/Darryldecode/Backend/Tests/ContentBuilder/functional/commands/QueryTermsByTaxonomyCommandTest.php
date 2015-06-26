<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/1/2015
 * Time: 12:07 PM
 */

use Darryldecode\Backend\Components\ContentBuilder\Models\Content;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeTaxonomy;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeTaxonomyTerm;
use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;

class QueryTermsByTaxonomyCommandTest extends TestCase {

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

    public function testShouldQueryTermsOfTheGivenTaxonomy()
    {
        $taxonomy = $this->createDummyData();

        // begin
        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\QueryTermsByTaxonomyCommand',
            array(
                'taxonomyId' => $taxonomy->id,
            )
        );

        // prove successful
        $this->assertTrue($result->isSuccessful());
        $this->assertEquals('Query terms by taxonomy command successful.', $result->getMessage());
        $this->assertEquals(200, $result->getStatusCode(), 'Status code should be ok');

        // prove contents
        $this->assertCount(3, $result->getData()->toArray(), "Should have 3 terms");
    }

    protected function createDummyData()
    {
        $cType = ContentType::create(array(
            'type' => 'Blog',
            'enable_revisions' => true
        ));

        $taxonomy = ContentTypeTaxonomy::create(array(
            'taxonomy' => 'categories',
            'description' => 'some description',
            'content_type_id' => $cType->id,
        ));

        // create now terms
        ContentTypeTaxonomyTerm::create(array(
            'term' => 'lifestyle',
            'slug' => 'lifestyle',
            'content_type_taxonomy_id' => $taxonomy->id,
        ));
        ContentTypeTaxonomyTerm::create(array(
            'term' => 'sports',
            'slug' => 'sports',
            'content_type_taxonomy_id' => $taxonomy->id,
        ));
        ContentTypeTaxonomyTerm::create(array(
            'term' => 'coding',
            'slug' => 'coding',
            'content_type_taxonomy_id' => $taxonomy->id,
        ));

        return $taxonomy;
    }
}