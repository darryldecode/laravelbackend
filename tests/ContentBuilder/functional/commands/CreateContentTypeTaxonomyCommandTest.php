<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/25/2015
 * Time: 2:59 PM
 */

use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeTaxonomy;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;

class CreateContentTypeTaxonomyCommandTest extends TestCase {

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

    public function testShouldDenyIfUserIsNotSuperUser()
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

        $contentType = $this->createContentType();

        // required type field
        $request = Request::create('','GET',array(
            'taxonomy' => '',
            'contentTypeId' => $contentType->id,
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentTypeTaxonomyCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals(403, $result->getStatusCode());
        $this->assertEquals('Not enough permission.', $result->getMessage());
    }

    public function testRequiredFields()
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

        $contentType = $this->createContentType();

        // required taxonomy field
        $request = Request::create('','GET',array(
            'taxonomy' => '',
            'description' => '',
            'contentTypeId' => $contentType->id,
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentTypeTaxonomyCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals('The taxonomy field is required.', $result->getMessage());

        // required contentTypeId field
        $request = Request::create('','GET',array(
            'taxonomy' => 'categories',
            'description' => '',
            'contentTypeId' => '',
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentTypeTaxonomyCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals('The content type id field is required.', $result->getMessage());

        // required contentTypeId field numeric
        $request = Request::create('','GET',array(
            'taxonomy' => 'categories',
            'description' => '',
            'contentTypeId' => 'not numeric',
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentTypeTaxonomyCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals('The content type id must be a number.', $result->getMessage());
    }

    public function testContentTypeNotValid()
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

        // required contentTypeId field numeric
        $request = Request::create('','GET',array(
            'taxonomy' => 'categories',
            'description' => '',
            'contentTypeId' => 3,
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentTypeTaxonomyCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals('Invalid Content Type.', $result->getMessage());
    }

    /**
     *
     */
    public function testContentTypeShouldBeCreatedWhenAllCheckPointsPassed()
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

        $contentType = $this->createContentType();

        // required taxonomy field
        $request = Request::create('','GET',array(
            'taxonomy' => 'categories',
            'description' => 'some description',
            'contentTypeId' => $contentType->id,
        ));

        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentTypeTaxonomyCommand',
            array(
                'taxonomy' => $request->get('taxonomy',null),
                'description' => $request->get('description',null),
                'contentTypeId' => $request->get('contentTypeId',null),
            )
        );

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals(201, $result->getStatusCode());
        $this->assertEquals('Content type taxonomy successfully created.', $result->getMessage());

        // verify content type taxonomy
        $cTypeTaxonomy = ContentTypeTaxonomy::find($result->getData()->id);

        $this->assertEquals($result->getData()->id, $cTypeTaxonomy->id);
        $this->assertEquals('categories', $cTypeTaxonomy->taxonomy);
    }

    protected function createContentType()
    {
        return ContentType::create(array(
            'type' => 'blog',
            'enable_revisions' => true
        ));
    }
}