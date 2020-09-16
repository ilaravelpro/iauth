<?php

Route::namespace('v1')->prefix('v1')->group(function() {
    Route::group(['middleware' => ['auth:api']], function () {
        if (iauth('methods.get.status')) Route::get('/me', 'AuthController@me')->name('api.iauth.get');
        if (iauth('methods.update.status')) Route::post('/me/{mode?}', 'AuthController@me_update')->name('api.iauth.update');
    });
    Route::prefix('iauth')->group(function() {
        if (iauth('methods.auth.status')) Route::post('', 'AuthController@auth')->name('api.iauth');
        if (iauth('methods.revoke.status')) Route::post('/revoke', 'AuthController@revoke')->name('api.iauth.revoke');
        Route::prefix('session')->group(function() {
            Route::post('/{session}', 'AuthSessionController@store')->name('api.iauth.session.store');
            Route::post('/{session}/{token}', 'AuthSessionController@verify')->name('api.iauth.session.verify');
            Route::post('/{session}/{token}/resend', 'AuthSessionController@resend')->name('api.iauth.session.resend');
        });
    });
});
