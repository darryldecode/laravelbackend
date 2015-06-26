<?php

Route::get('/dashboard', array(
    'before' => array(),
    'as' => 'dashboard',
    'uses' => 'DashboardController@index'
));