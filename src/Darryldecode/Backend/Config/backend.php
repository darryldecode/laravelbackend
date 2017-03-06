<?php

return [

    /*
     * The backend base url
     */
    'base_url' => 'backend',

    /*
     * The login url
     */
    'login_route' => 'login', // this will be "backend/login"

    /*
     * Disabled widgets
     */
    'disabled_widgets' => array(
        'Dashboard Welcome Message'
    ),

    /*
     * The title to be use on Backend
     */
    'backend_title' => 'Laravel Backend',

    /*
     * Media Manager thumbnails
     */
    'thumb_sizes' => array(
        'small' => array(150,120),
        'medium' => array(300,200),
        'large' => array(600,450),
    ),

    /*
     * built-in component models being used
     *
     * NOTE:
     *
     * The purpose of this is for extensibility, if you want to extend relationships for user/content model
     * you can change this to your own and make sure to extend this models
     */
    'user_model'    => 'Darryldecode\Backend\Components\User\Models\User',
    'content_model' => 'Darryldecode\Backend\Components\ContentBuilder\Models\Content',

    /*
     * Before Backend Access Hook
     *
     * Here you can check if user is in groups or has permissions to redirect to any route
     * you want when it does not matches your criteria to access the backend
     */
    'before_backend_access' => function($user) {

    }
];