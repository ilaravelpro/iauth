<?php

Route::namespace('v1')->prefix('v1')->group(function() {
    Route::group(['middleware' => ['auth:api']], function () {
        if (iauth('methods.get.status')) Route::get('/me', 'AuthController@me')->name('api.auth.get');
        if (iauth('methods.update.status')) Route::post('/me/{mode?}', 'AuthController@me_update')->name('api.auth.update');
    });
    Route::prefix('iauth')->group(function() {
        if (iauth('methods.auth.status')) Route::post('', 'AuthController@auth')->name('api.auth');
        if (iauth('methods.revoke.status')) Route::post('/revoke', 'AuthController@revoke')->name('api.auth.revoke');
    });
});
