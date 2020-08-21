<?php

Route::group([

    'prefix' => 'auth'

], function () {

    Route::post('login', 'AuthController@login');
    Route::post('register', 'AuthController@register');
    Route::post('logout', 'AuthController@logout');

    Route::get('user', 'AuthController@user');
});

Route::group([

    'prefix' => 'account'

], function () {

    Route::post('/', 'AccountController@create');

    Route::get('/', 'AccountController@getAll');
    Route::get('/{account_id}', 'AccountController@getOne');

    Route::put('/{account_id}', 'AccountController@update');

    Route::delete('/', 'AccountController@delete');
});
