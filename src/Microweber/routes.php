<?php
Route::group(['namespace' => '\Microweber\Controllers'], function () {
    Route::any('/', 'DefaultController@index');
    Route::controller('alou', 'TestController');
    
    //Route::any('/{slug}', 'DefaultController@index');
    //Route::any('/apijs/{slug}', 'DefaultController@apijs');
    Route::any('/api', 'DefaultController@api');
    Route::any('/api/{slug}', 'DefaultController@api');
    
    //Route::any('/admin', 'DefaultController@admin');
    //Route::any('admin/{all}', function(){
    //    return 'DefaultController@admin';
    //})->where('all', '/.*');
    //Route::any('/admin', 'AdminController@index');
    Route::any('/admin', 'AdminController@index');
    Route::any('admin', array('as' => 'admin', 'uses' => 'AdminController@index'));
    
    //Route::any('/admin/{slashData?}', 'AdminController@index')
    //    ->where('slashData', '(.*)');
    Route::any('admin/{all}', array('as' => 'admin', 'uses' => 'AdminController@index'))->where('all', '.*');;
    
    //Route::group(array('prefix' => 'admin', 'namespace' => 'Admin', 'before' => 'auth'), function()
    //{
    //    Route::get('/', 'DefaultController@admin');
    //});
    //Route::get('/', function()
    //{
    //	return View::make('hello');
    //});
    Route::any('api/{all}', array('as' => 'api', 'uses' => 'DefaultController@api'))->where('all', '.*');;
    Route::any('/apijs', 'DefaultController@apijs');
    Route::any('/apijs_settings', 'DefaultController@apijs_settings');
    Route::any('apijs/{all}', array('as' => 'apijs', 'uses' => 'DefaultController@apijs'))->where('all', '.*');
    Route::any('api_html/{all}', array('as' => 'api', 'uses' => 'DefaultController@api_html'))->where('all', '.*');;
    Route::any('/api_html', 'DefaultController@api_html');
    Route::any('/apijs_settings', 'DefaultController@apijs_settings');
    Route::any('apijs/{all}', array('as' => 'apijs', 'uses' => 'DefaultController@apijs'))->where('all', '.*');
    Route::any('/editor_tools', 'DefaultController@editor_tools');
    Route::any('editor_tools/{all}', array('as' => 'editor_tools', 'uses' => 'DefaultController@editor_tools'))->where('all', '.*');
    Route::any('/plupload', 'ModuleController@plupload');
    Route::any('plupload/{all}', array('as' => 'plupload', 'uses' => 'ModuleController@plupload'))->where('all', '.*');;
    Route::any('/module/', 'ModuleController@index');
    Route::any('module/{all}', array('as' => 'module', 'uses' => 'ModuleController@index'))->where('all', '.*');;
    Route::any('{all}', array('as' => 'all', 'uses' => 'DefaultController@index'))->where('all', '.*');;
    
    //Route::any('api/{all}', function(){
    //    return 'DefaultController@api';
    //})->where('all', '.*');
    //Route::any('{all}', function(){
    //    return 'DefaultController@indsssex';
    //})->where('all', '.*');
    
});
