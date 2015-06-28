<?php

// Authentication routes
Route::group(array('prefix'=> config('backend.backend.login_route')), function()
{
    Route::get('/', array(
        'before' => array(),
        'as' => 'backend.authManager.getLogin',
        'uses' => 'AuthController@getLogin'
    ));
    Route::post('/', array(
        'before' => array(),
        'as' => 'backend.authManager.postLogin',
        'uses' => 'AuthController@postLogin'
    ));
});

// logout route
Route::get('logout', 'AuthController@getLogout');

// Password reset link request routes...
Route::get('password/email', 'PasswordController@getEmail');
Route::post('password/email', 'PasswordController@postEmail');

// Password reset routes...
Route::get('password/reset/{token}', 'PasswordController@getReset');
Route::post('password/reset', 'PasswordController@postReset');