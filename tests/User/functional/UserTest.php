<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/25/2015
 * Time: 10:24 AM
 */

use Darryldecode\Backend\Components\User\Models\Group;
use Darryldecode\Backend\Components\User\Models\User;
use Faker\Factory as Faker;

class UserTest extends TestCase {

    protected $application;

    protected $faker;

    public function setUp()
    {
        $this->faker = Faker::create();
        $this->application = $this->createApplication();
        $this->application['config']->set('database.connections.sqlite.database',':memory:');
        $this->application['db']->setDefaultConnection('sqlite');
        $this->application->make('Illuminate\Contracts\Console\Kernel')->call('migrate');
    }

    public function tearDown()
    {

    }

    public function testValidationShouldFailIfNoFirstNameIsSupplied()
    {
        $userData = array(
            'first_name' => ''
        );

        $v = $this->application['validator']->make($userData, User::$rules, array());

        $this->assertTrue($v->fails(), 'Validation should Fail');
        $this->assertEquals('The first name field is required.', $v->messages()->first());
    }

    public function testValidationShouldFailIfInvalidEmailIsSupplied()
    {
        $userData = array(
            'first_name' => 'Darryl',
            'last_name' => 'Fernandez',
            'email' => 'Darryl',
        );

        $v = $this->application['validator']->make($userData, User::$rules, array());

        $this->assertTrue($v->fails(), 'Validation should Fail');
        $this->assertEquals('The email must be a valid email address.', $v->messages()->first());
    }

    public function testEmailShouldBeUnique()
    {
        // create first our user
        $user1 = User::create(array(
            'first_name' => 'darryl',
            'last_name' => 'Fernandez',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
        ));

        // now lets validate our first user with the same email
        $user2 = array(
            'first_name' => 'darryl',
            'last_name' => 'Fernandez',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
        );

        $v = $this->application['validator']->make($user2, User::$rules, array());

        $this->assertTrue($v->fails(), 'Validation should Fail');
        $this->assertEquals('The email has already been taken.', $v->messages()->first());
    }

    public function testShouldFailIfPasswordIsNotSuppliedAndShouldHaveMinimumOfEightCharacters()
    {
        // create first our user
        $user1 = User::create(array(
            'first_name' => 'darryl',
            'last_name' => 'Fernandez',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
        ));

        // now lets validate our first user with the same email
        $user2 = array(
            'first_name' => 'jane',
            'last_name' => 'Fernandez',
            'email' => 'jane@gmail.com',
            'password' => 'short',
        );

        $v = $this->application['validator']->make($user2, User::$rules, array());

        $this->assertTrue($v->fails(), 'Validation should Fail');
        $this->assertEquals('The password must be at least 8 characters.', $v->messages()->first());
    }

    public function testUserHasPermission()
    {
        // create first our user
        $user1 = User::create(array(
            'first_name' => 'darryl',
            'last_name' => 'Fernandez',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'blog.create' => 1,
                'blog.edit' => 1,
                'blog.delete' => -1,
            )
        ));

        $this->assertFalse($user1->hasPermission('blog.delete'), 'User should not have permission blog.delete');
        $this->assertTrue($user1->hasPermission('blog.create'), 'User should have permission blog.create');
        $this->assertTrue($user1->hasPermission('blog.edit'), 'User should have permission blog.edit');
    }

    public function testUserHasAnyPermission()
    {
        // create first our user
        $user1 = User::create(array(
            'first_name' => 'darryl',
            'last_name' => 'Fernandez',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'blog.create' => 1,
                'blog.edit' => 1,
                'blog.delete' => -1,
            )
        ));

        $this->assertTrue($user1->hasAnyPermission(['blog.delete','blog.create']));
    }

    public function testUserShouldInheritPermission()
    {
        $group = Group::create(array(
            'name' => 'blogger',
            'permissions' => array(
                'blog.create' => 1,
                'blog.edit' => 1,
                'blog.delete' => -1,
            )
        ));

        $user = User::create(array(
            'first_name' => 'darryl',
            'last_name' => 'Fernandez',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'blog.create' => 0, // this should inherit to its permission in group
                'forum.create' => 0, // this should inherit to its permission in group but in this case, this doe not exist either on its group so this should have a deny
            )
        ));

        $user->groups()->attach($group);

        $this->assertTrue($user->hasPermission('blog.create'), 'User should have permission blog.crete inherited from its group');

        // get users overall permissions
        $userCombinedPermissions = $user->getCombinedPermissions();

        $this->assertEquals(1, $userCombinedPermissions['blog.create'], 'Should be 1 as inherited from its group');
        $this->assertEquals(-1, $userCombinedPermissions['forum.create'], 'Should be -1 as it does not exist on its group');
    }

    public function testUserPermissionsInThatBelongsToMultipleGroup()
    {
        $blogger = Group::create(array(
            'name' => 'blogger',
            'permissions' => array(
                'blog.list' => 1,
                'blog.create' => 1,
                'blog.edit' => 1,
                'blog.delete' => -1,
            )
        ));

        $artist = Group::create(array(
            'name' => 'artist',
            'permissions' => array(
                'blog.list' => -1,
                'art.create' => 1,
                'art.edit' => 1,
                'art.delete' => -1,
            )
        ));

        $user = User::create(array(
            'first_name' => 'darryl',
            'last_name' => 'Fernandez',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'blog.create' => 0, // this should inherit to its permission in group
                'forum.create' => 0, // this should inherit to its permission in group but in this case, this doe not exist either on its group so this should have a deny
            )
        ));

        $user->groups()->attach($blogger);
        $user->groups()->attach($artist);

        // our user now belongs to two groups namely blogger and artist
        // in this case, both blogger and artist has permission "blog.list" but with different value
        // in blogger group, "blog.list" is allowed while on artist group it is denied
        // in cases like this, it will be a deny
        $this->assertFalse($user->hasPermission('blog.list'));

        // now that user don't have permission "blog.list" because 1 of its group denies it
        // we will update the user to have its own (special) permission "blog.list" as allowed
        // then the user should have now that permission because its on his (special) permissions have more priority
        // than the permissions the user acquired from his group
        $user->permissions = array(
            'blog.list' => 1, // now add this
            'blog.create' => 0,
            'forum.create' => 0,
        );
        $user->save();

        // the user should have now permission "blog.list" even if this permission is denied to its group
        $this->assertTrue($user->hasPermission('blog.list'));
    }

    public function testUserInGroup()
    {
        $blogger = Group::create(array(
            'name' => 'blogger',
            'permissions' => array(
                'blog.list' => 1,
                'blog.create' => 1,
                'blog.edit' => 1,
                'blog.delete' => -1,
            )
        ));

        $artist = Group::create(array(
            'name' => 'artist',
            'permissions' => array(
                'blog.list' => -1,
                'art.create' => 1,
                'art.edit' => 1,
                'art.delete' => -1,
            )
        ));

        $user = User::create(array(
            'first_name' => 'darryl',
            'last_name' => 'Fernandez',
            'email' => 'darryl@gmail.com',
            'password' => 'pass$darryl',
            'permissions' => array(
                'blog.create' => 0,
                'forum.create' => 0,
            )
        ));

        $user->groups()->attach($blogger);
        $user->groups()->attach($artist);

        // by using the group object
        $this->assertTrue($user->inGroup($blogger));
        // by using the group name
        $this->assertTrue($user->inGroup($blogger->name));
    }
}