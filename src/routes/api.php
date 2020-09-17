<?php

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
            Route::get('/{session}/{token}/{pin}', 'AuthSessionController@verify')->name('api.iauth.session.verify');
            Route::get('/{session}/{token}', 'AuthSessionController@resend')->name('api.iauth.session.resend');
            Route::get('/agent', function () {
                $client = new \Memcached();
                $client->addServer('localhost', 11211);

                $pool = new \Cache\Adapter\Memcached\MemcachedCachePool($client);

                $result = new WhichBrowser\Parser();
                $result->setCache($pool);
                $result->analyse(getallheaders());
            });
        });
    });
});
