<?php

Route::namespace('v1')->prefix('v1')->group(function() {
    Route::group(['middleware' => ['auth:api']], function () {
        if (iauth('modes.get')) Route::get('/me', 'AuthController@me')->name('api.auth.get');
        if (iauth('modes.update')) Route::post('/me', 'AuthController@me_update')->name('api.auth.update');
    });
    Route::prefix('iauth')->group(function() {
        if (iauth('modes.authorize.status')) Route::post('authorize', 'AuthController@login')->name('api.auth.authorize');
        if (iauth('modes.logout')) Route::post('logout', 'AuthController@logout')->name('api.auth.logout');
        if (iconfig('auth.register')) Route::post('register', 'AuthController@register')->name('api.auth.register');
    });
});
