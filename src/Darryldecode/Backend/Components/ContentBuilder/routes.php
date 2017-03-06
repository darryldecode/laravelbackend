<?php

Route::group(array('prefix'=> 'content_types'), function()
{
    Route::get('/', array(
        'before' => array(),
        'as' => 'backend.contentTypes',
        'uses' => 'ContentTypeController@index'
    ));
    Route::post('/', array(
        'before' => array(),
        'as' => 'backend.contentTypes.postCreate',
        'uses' => 'ContentTypeController@postCreate'
    ));
    Route::delete('/{contentTypeId}', array(
        'before' => array(),
        'as' => 'backend.contentTypes.delete',
        'uses' => 'ContentTypeController@delete'
    ));
});

Route::group(array('prefix'=> 'content_taxonomies'), function()
{
    Route::post('/', array(
        'before' => array(),
        'as' => 'backend.contentTaxonomies.postCreate',
        'uses' => 'ContentTaxonomyController@postCreate'
    ));
    Route::delete('/{taxonomyId}', array(
        'before' => array(),
        'as' => 'backend.contentTaxonomies.delete',
        'uses' => 'ContentTaxonomyController@delete'
    ));
});

Route::group(array('prefix'=> 'taxonomy_terms'), function()
{
    Route::get('/', array(
        'before' => array(),
        'as' => 'backend.contentTaxonomyTerm.getByTaxonomyId',
        'uses' => 'ContentTaxonomyTermsController@getByTaxonomyId'
    ));
    Route::post('/', array(
        'before' => array(),
        'as' => 'backend.contentTaxonomyTerm.postCreate',
        'uses' => 'ContentTaxonomyTermsController@postCreate'
    ));
    Route::delete('/{termId}', array(
        'before' => array(),
        'as' => 'backend.contentTaxonomyTerm.delete',
        'uses' => 'ContentTaxonomyTermsController@delete'
    ));
});

Route::group(array('prefix'=> 'custom_fields'), function()
{
    Route::get('/', array(
        'before' => array(),
        'as' => 'backend.customFields',
        'uses' => 'ContentTypeFormGroupController@index'
    ));
    Route::post('/', array(
        'before' => array(),
        'as' => 'backend.customFields.postCreate',
        'uses' => 'ContentTypeFormGroupController@postCreate'
    ));
    Route::put('/{formGroupId}', array(
        'before' => array(),
        'as' => 'backend.customFields.putUpdate',
        'uses' => 'ContentTypeFormGroupController@putUpdate'
    ));
    Route::delete('/{formGroupId}', array(
        'before' => array(),
        'as' => 'backend.customFields.delete',
        'uses' => 'ContentTypeFormGroupController@delete'
    ));
});

Route::group(array('prefix'=> 'contents'), function() {
    Route::get('/{contentType}', array(
        'before' => array(),
        'as' => 'backend.contents',
        'uses' => 'ContentController@getByType'
    ));
    Route::post('/', array(
        'before' => array(),
        'as' => 'backend.contents.postCreate',
        'uses' => 'ContentController@postCreate'
    ));
    Route::put('/{contentTypeId}', array(
        'before' => array(),
        'as' => 'backend.contents.putUpdate',
        'uses' => 'ContentController@putUpdate'
    ));
    Route::delete('/{contentId}', array(
        'before' => array(),
        'as' => 'backend.contents.delete',
        'uses' => 'ContentController@delete'
    ));
});