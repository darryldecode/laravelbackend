<?php

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
        $this->seedSampleContentTypes();

        Model::reguard();
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

    protected function seedSampleContentTypes()
    {
        ContentType::create(array(
            'type' => 'blog',
            'enable_revisions' => true
        ));
    }
}
