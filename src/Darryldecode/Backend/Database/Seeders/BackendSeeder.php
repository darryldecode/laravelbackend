<?php namespace Darryldecode\Backend\Database\Seeders;

use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Faker\Factory as Faker;

class BackendSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->seedUser();
        $this->seedDummyUsers();
        $this->seedDummyContentTypes();
    }

    protected function seedUser()
    {
        $group = \Darryldecode\Backend\Components\User\Models\Group::create(array(
            'name' => 'Super User',
            'permissions' => array(
                'superuser' => 1
            )
        ));

        $user = \Darryldecode\Backend\Components\User\Models\User::create(array(
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'admin@gmail.com',
            'password' => 'admin',
            'permissions' => array(
                'superuser' => 1
            ),
        ));

        $user->groups()->attach($group);
    }

    protected function seedDummyUsers()
    {
        $faker = Faker::create();

        foreach(range(0,30) as $i)
        {
            $user = \Darryldecode\Backend\Components\User\Models\User::create(array(
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'email' => $faker->email,
                'password' => $faker->word,
                'permissions' => array(
                ),
            ));
        }
    }

    protected function seedDummyContentTypes()
    {
        ContentType::create(array(
            'type' => 'Blog',
            'enable_revisions' => true
        ));
        ContentType::create(array(
            'type' => 'Events',
            'enable_revisions' => true
        ));
    }
}
