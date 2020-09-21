<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/17/20, 5:52 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

Route::namespace('v1')->prefix('v1')->group(function() {
    Route::group(['middleware' => ['auth:api']], function () {
        if (iauth('methods.get.status')) Route::get('/me', 'AuthController@me')->name('api.iauth.get');
        if (iauth('methods.update.status')) Route::post('/me/{mode?}', 'AuthController@me_update')->name('api.iauth.update');
    });
    Route::prefix('iauth')->group(function() {
        Route::prefix('session')->group(function() {
            Route::group(['middleware' => ['auth:apiIf']], function () {
                Route::delete('/{session}/{token?}', 'AuthController@revoke')->name('api.iauth.session.revoke');
            });
            Route::post('/{session}', 'AuthController@store')->name('api.iauth.session.store');
            Route::post('/{session}/{token}/{pin}', 'AuthController@verify')->name('api.iauth.session.verify');
            Route::get('/{session}/{token}', 'AuthController@resend')->name('api.iauth.session.resend');
        });
    });
});
