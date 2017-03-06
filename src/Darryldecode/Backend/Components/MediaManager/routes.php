<?php

Route::group(array('prefix'=> 'media_manager'), function()
{
    Route::get('/', array(
        'before' => array(),
        'as' => 'backend.mediaManager',
        'uses' => 'MediaManagerController@ls'
    ));
    Route::post('/mkDir', array(
        'before' => array(),
        'as' => 'backend.mediaManager.postMkDir',
        'uses' => 'MediaManagerController@postMkDir'
    ));
    Route::post('/upload', array(
        'before' => array(),
        'as' => 'backend.mediaManager.postUpload',
        'uses' => 'MediaManagerController@postUpload'
    ));
    Route::delete('/rm', array(
        'before' => array(),
        'as' => 'backend.mediaManager.delete',
        'uses' => 'MediaManagerController@delete'
    ));
    Route::delete('/rmrf', array(
        'before' => array(),
        'as' => 'backend.mediaManager.deleteDirectory',
        'uses' => 'MediaManagerController@deleteDirectory'
    ));
    Route::post('/mv', array(
        'before' => array(),
        'as' => 'backend.mediaManager.postMove',
        'uses' => 'MediaManagerController@postMove'
    ));
    Route::get('/download', array(
        'before' => array(),
        'as' => 'backend.mediaManager.getDownload',
        'uses' => 'MediaManagerController@getDownload'
    ));
});