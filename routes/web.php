<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::model('notification', 'Notification');

Route::get('/search/suggest', ['as' => 'searchSuggest', 'uses' => 'SearchController@suggest']);
Route::get('/search/{keyword?}', ['as' => 'search', 'uses' => 'SearchController@search']);
Route::get('/search/{type?}/{keyword?}', ['as' => 'searchKeywordType', 'uses' => 'SearchController@searchKeywordType']);

Route::get('/recent', ['as' => 'recent', 'uses' => 'RecentController@recent']);

Route::get('/reports', ['as' => 'reports', 'uses' => 'ReportsController@reports']);
Route::post('/reports/dismiss', ['as' => 'reportDismiss', 'before' => ['csrf'], 'uses' => 'ReportsController@dismiss']);

Route::get('/login', ['as' => 'login', 'uses' => 'UsersController@login']);
Route::get('/logout', ['as' => 'logout', 'uses' => 'UsersController@logout']);
Route::get('/authcheck', ['as' => 'authcheck', 'uses' => 'UsersController@authcheck']);

Route::group(['middleware' => 'auth'], function () {
    Route::post('/user/notifications/watch', ['uses' => 'UsersController@toggleWatch']);
    Route::get('/user/notifications', ['as' => 'notifications', 'uses' => 'UsersController@notifications']);
    Route::post('/user/notifications/dismiss', ['as' => 'notificationDismiss', 'middleware' => 'csrf', 'uses' => 'UsersController@dismiss']);
    Route::get('/user/notifications/download/{notification}/{filename}', ['as' => 'notificationDownload', 'uses' => 'UsersController@downloadDismiss']);
});

// XXX has internal auth that supports basic
Route::get('/user/watched.opml', ['as' => 'opml', 'uses' => 'UsersController@opml']);

Route::get('/api/muid/{muId}', ['uses' => 'ApiController@muid']);
Route::get('/api/register', ['uses' => 'ApiController@register']);
Route::get('/api/changepassword', ['uses' => 'ApiController@changePassword']);

Route::get('/admin/flushcache', ['before' => 'auth.super', 'uses' => 'AdminController@flushCache']);

Route::get('/reader/image', ['as' => 'readerImage', 'uses' => 'ReaderController@image']);
Route::get('/reader/{path}', ['as' => 'reader', 'uses' => 'ReaderController@read']);

Route::get('/donate', ['as' => 'donate', 'uses' => function () {
    return view('donate', ['pageTitle' => 'Donate']);
}]);

Route::post('/path/report', ['before' => 'csrf|auth', 'as' => 'report', 'uses' => 'IndexController@report']);
Route::post('/path/save', ['middleware' => 'csrf', 'uses' => 'IndexController@save']);
Route::get('/', ['as' => 'home', 'uses' => 'IndexController@index']);
Route::get('{path}', ['uses' => 'IndexController@index'])->where('path', '^.*');
