<?php

Route::group(array('prefix'=> 'media'), function()
{
    Route::get('/', array(
        'before' => array(),
        'as' => 'backend.media',
        'uses' => 'MediaController@index'
    ));
    Route::any('/elFinder', array(
        'before' => array(),
        'as' => 'backend.media',
        'uses' => 'MediaController@elFinder'
    ));
});