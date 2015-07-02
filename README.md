# LARAVEL BACKEND (still on active development)

Laravel Backend is an instant backend for laravel 5. It is a component base architecture for its several built in components and is very flexible.

### Built-in Components:
  - User Management with Roles & Permissions (base on individual module), throotling
  - Content Type Builder w/ Custom Fields (inspired from WordPress)
  - Navigation Builder
  - Media Manager

### INSTALLATION
---
**--NOTE IMPORTANT--**

- make sure to set all your database connection first.
- make sure to set correct "url" on config/app.php

---

***Step 1:*** On your composer.json, add this on require block:
```
"require": {
    "darryldecode/laravelbackend": "dev-master"
}
```
then you can do: composer update "darryldecode/laravelbackend" to update/install the package

---
***Step 2:***
- add this lines in config/app.php on providers array:
```
Darryldecode\Backend\BackendServiceProvider::class,
Darryldecode\Backend\BackendRoutesServiceProvider::class,
```

- add this lines in config/app.php on aliases array:
```
'Form' => Illuminate\Html\FormFacade::class,
'Html' => Illuminate\Html\HtmlFacade::class,
```
---
***Step 3:***
> **NOTE:** Delete all default migration first bundled with your laravel installation. Backend package has its own full-blown user component. After you have deleted it, do this on your command line:

```
php artisan vendor:publish --provider="Darryldecode\Backend\BackendServiceProvider"
```

Then do:
```
composer dump-autoload
```
---

***Step 5:***

On your terminal, do:
```
php artisan migrate
php artisan db:seed --class=BackendSeeder
```
---
***Step 6:***
- on config/auth.php
```
changed your model to: 'Darryldecode\Backend\Components\User\Models\User'
```
- on config/filesystems.php
```
changed the local disks:
            'root'   => storage_path('app'),
        to:
            'root'   => public_path('uploads'),
```

---

***CONRGATULATIONS!*** Your instant laravel 5 backend is ready! you can login by navigating to: ```/backend/login```

- user: admin@gmail.com
- pass: admin

Please change your credentials. You can change your backend url on config.

---

***TESTS:***

You can run the tests to make sure all functionality is good.
first: open your ```phpunit.xml``` config and add this to your test suites:

    <testsuite name="Backend">
        <directory>./vendor/darryldecode/backend/tests/</directory>
    </testsuite>

then run: ```"phpunit --testsuite Backend"``` on your command line of choice.

-------------------------------------------------------------

Next? Read the full documentation to get the most of the package! Extending is easy aswell! Enjoy!

### NOTE: 

> PACKAGE IS STILL IN ALPHA AND STILL IN ACTIVE DEVELOPMENT! **Documenation coming soon!**
