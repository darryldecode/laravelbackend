<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/25/2015
 * Time: 2:36 PM
 */

use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;

class CreateContentTypeCommandTest extends TestCase {

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

        // required type field
        $request = Request::create('','GET',array(
            'type' => '',
            'enableRevision' => '',
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentTypeCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals(403, $result->getStatusCode());
        $this->assertEquals('Not enough permission.', $result->getMessage());
    }

    public function testValidateRequiredFields()
    {
        // this is a super user
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'superuser' => 1,
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        // required type field
        $request = Request::create('','GET',array(
            'type' => '',
            'enableRevision' => '',
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentTypeCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals('The type field is required.', $result->getMessage());

        // required type field
        $request = Request::create('','GET',array(
            'type' => '',
            'enableRevision' => '',
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentTypeCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals('The type field is required.', $result->getMessage());
    }

    public function testShouldNowCreateWhenAllCheckPointsPassed()
    {
        // this is a super user
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'superuser' => 1,
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        // required type field
        $request = Request::create('','GET',array(
            'type' => '',
            'enableRevision' => '',
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentTypeCommand',
            $request
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals('The type field is required.', $result->getMessage());

        // required type field
        $request = Request::create('','GET',array(
            'type' => 'blog',
            'enableRevision' => true,
        ));

        $result = $this->commandDispatcher->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentTypeCommand',
            $request
        );

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals(201, $result->getStatusCode());
        $this->assertEquals('Content type successfully created.', $result->getMessage());
    }

    public function testCreateContentTypeWithEnabledRevisions()
    {
        // this is a super user
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'superuser' => 1,
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentTypeCommand',
            array(
                'type' => 'Products',
                'enableRevision' => 'yes',
            )
        );

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals(201, $result->getStatusCode());
        $this->assertEquals('Content type successfully created.', $result->getMessage());

        // prove
        $cType = ContentType::find($result->getData()->id);
        $this->assertTrue((bool) $cType->enable_revisions);
    }

    public function testCreateContentTypeWithDisabledRevisions()
    {
        // this is a super user
        $user = User::create(array(
            'first_name' => 'darryl',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'superuser' => 1,
            )
        ));

        $this->application['auth']->loginUsingId($user->id);

        $result = $this->commandDispatcher->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentTypeCommand',
            array(
                'type' => 'Products',
                'enableRevision' => 'no',
            )
        );

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals(201, $result->getStatusCode());
        $this->assertEquals('Content type successfully created.', $result->getMessage());

        // prove
        $cType = ContentType::find($result->getData()->id);
        $this->assertFalse((bool) $cType->enable_revisions);
    }
}