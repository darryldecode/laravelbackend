<?php

Route::group(array('prefix'=> 'navigation'), function()
{
    Route::get('/', array(
        'before' => array(),
        'as' => 'backend.navigation',
        'uses' => 'NavigationController@index'
    ));
    Route::get('/builder', array(
        'before' => array(),
        'as' => 'backend.navigation.getNavBuilderDisplay',
        'uses' => 'NavigationController@getNavBuilderDisplay'
    ));
    Route::post('/builder', array(
        'before' => array(),
        'as' => 'backend.navigation.postCreate',
        'uses' => 'NavigationController@postCreate'
    ));
    Route::put('/builder/{id}', array(
        'before' => array(),
        'as' => 'backend.navigation.putUpdate',
        'uses' => 'NavigationController@putUpdate'
    ));
    Route::delete('/builder/{id}', array(
        'before' => array(),
        'as' => 'backend.navigation.delete',
        'uses' => 'NavigationController@delete'
    ));
});