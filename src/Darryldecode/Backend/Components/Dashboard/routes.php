<?php

Route::get('/dashboard', array(
    'before' => array(),
    'as' => 'dashboard',
    'uses' => 'DashboardController@index'
));
Route::get('/dashboard/info', array(
    'before' => array(),
    'as' => 'dashboard.info',
    'uses' => 'DashboardController@info'
));