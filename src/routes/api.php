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
        if (iauth('methods.auth.status')) Route::post('', 'AuthController@auth')->name('api.iauth');
        if (iauth('methods.revoke.status')) Route::post('/revoke', 'AuthController@revoke')->name('api.iauth.revoke');
        Route::prefix('sessions')->group(function() {
            Route::group(['middleware' => ['auth:apiIf']], function () {
                Route::delete('/{session}/{token?}', 'AuthSessionController@revoke')->name('api.iauth.session.revoke');
            });
            Route::post('/{session}', 'AuthSessionController@store')->name('api.iauth.session.store');
            Route::post('/{session}/{token}/{pin}', 'AuthSessionController@verify')->name('api.iauth.session.verify');
            Route::get('/{session}/{token}', 'AuthSessionController@resend')->name('api.iauth.session.resend');
            Route::get('/agent', function () {
                $ua = "Mozilla/5.0 (Linux; Android 6.0.1; ASUS_Z00LD) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.101 Mobile Safari/537.36";
                //$ua = "Mozilla/5.0 (X11; CrOS x86_64 11895.118.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.159 Safari/537.36";
                $parser = \UAParser\Parser::create();
                $result = $parser->parse($ua);
                return [
                    'browser' => $result->ua,
                    'os' => $result->os,
                    'device' => $result->device,
                    'origi' => $ua
                ];
            });
        });
    });
});
