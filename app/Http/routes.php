<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {

    Route::get('/', 'ProductsController@index');

    Route::get('/products/feed', 'ProductsController@feed');

    // ToDo
    // Route::get('/products/feed/{directory}', 'ProductsFeedController@directory');
});

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| This route group applies the "api" middleware group to every route
| it contains. The "api" middleware group is defined in your HTTP
| kernel and includes request throttling.
|
*/

Route::group(['prefix' => 'products/feed'], function () {

    Route::post('process', [
        'as'   => 'products.feed.process',
        'uses' => 'ProductsFeedController@process'
    ])->middleware(['api', 'no-timeout']);

    Route::post('display', 'ProductsFeedController@display');

    Route::post('display/product', 'ProductsFeedController@displayById');
});
