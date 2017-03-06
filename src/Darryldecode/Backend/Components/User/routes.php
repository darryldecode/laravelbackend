<?php

Route::group(array('prefix'=> 'users'), function()
{
    Route::get('/', array(
        'before' => array(),
        'as' => 'backend.users',
        'uses' => 'UserController@index'
    ));
    Route::get('/available_permissions', array(
        'before' => array(),
        'as' => 'backend.users.getAvailablePermissions',
        'uses' => 'UserController@getAvailablePermissions'
    ));
    Route::post('/', array(
        'before' => array(),
        'as' => 'backend.users.postCreate',
        'uses' => 'UserController@postCreate'
    ));
    Route::put('/{userId}', array(
        'before' => array(),
        'as' => 'backend.users.putUpdate',
        'uses' => 'UserController@putUpdate'
    ));
    Route::delete('/{userId}', array(
        'before' => array(),
        'as' => 'backend.users.delete',
        'uses' => 'UserController@delete'
    ));
});

Route::group(array('prefix'=> 'groups'), function()
{
    Route::get('/', array(
        'before' => array(),
        'as' => 'backend.groups',
        'uses' => 'GroupController@index'
    ));
    Route::post('/', array(
        'before' => array(),
        'as' => 'backend.groups.postGroups',
        'uses' => 'GroupController@postCreate'
    ));
    Route::put('/{groupId}', array(
        'before' => array(),
        'as' => 'backend.groups.putGroup',
        'uses' => 'GroupController@putUpdate'
    ));
    Route::delete('/{groupId}', array(
        'before' => array(),
        'as' => 'backend.groups.delete',
        'uses' => 'GroupController@delete'
    ));
});