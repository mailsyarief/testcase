<?php

Route::group([

    'prefix' => 'auth'

], function () {

    Route::post('login', 'AuthController@login');
    Route::post('register', 'AuthController@register');
    Route::post('logout', 'AuthController@logout');

    Route::get('user', 'AuthController@user');
    Route::get('users', 'AuthController@users');
    Route::get('user/{user_id}', 'AuthController@userDetail');
});

Route::group([

    'prefix' => 'account'

], function () {

    Route::post('/', 'AccountController@create');
    Route::post('/restore/{account_id}', 'AccountController@restore');

    Route::get('/', 'AccountController@getAll');
    Route::get('/deleted', 'AccountController@getDeleted');
    Route::get('/{account_id}', 'AccountController@getOne');

    Route::put('/{account_id}', 'AccountController@update');

    Route::delete('/{account_id}', 'AccountController@delete');
});

Route::group([

    'prefix' => 'transaction'

], function () {

    Route::post('/', 'TransactionController@create');
    Route::post('/restore/{transaction_id}', 'TransactionController@restore');

    Route::get('/', 'TransactionController@getAll');
    Route::get('/deleted', 'TransactionController@getDeleted');
    Route::get('/{transaction_id}', 'TransactionController@getOne');
    Route::get('/summary/monthly', 'TransactionController@getSummaryMonthly');
    Route::get('/summary/daily', 'TransactionController@getSummaryDaily');

    Route::put('/{transaction_id}', 'TransactionController@update');

    Route::delete('/{transaction_id}', 'TransactionController@delete');
});
