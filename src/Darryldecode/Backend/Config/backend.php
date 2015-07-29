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
];