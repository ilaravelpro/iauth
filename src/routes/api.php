<?php

Route::namespace('v1')->prefix('v1')->group(function() {
    Route::group(['middleware' => ['auth:api']], function () {
        if (iauth('modes.get')) Route::get('/me', 'AuthController@me')->name('api.auth.get');
        if (iauth('modes.update')) Route::post('/me/{mode?}', 'AuthController@me_update')->name('api.auth.update');
    });
    Route::prefix('iauth')->group(function() {
        if (iauth('modes.authorize.status')) Route::post('authorize', 'AuthController@authorizing')->name('api.auth.authorize');
        if (iauth('modes.revoke')) Route::post('revoke', 'AuthController@revoke')->name('api.auth.revoke');
    });
});
