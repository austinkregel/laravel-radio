<?php

Route::group(['prefix' => 'radio', 'as' => 'radio::'], function(){
    Route::get('/', ['middleware' => config('kregel.radio.middleware') ,'uses' =>function(){
        return view('radio::broadcast');
    }]);
    Route::group(['prefix' => 'api', 'as' => 'api.', 'middleware' => config('kregel.radio.middleware-api')], function (){
        Route::post('notification', ['middleware' => 'jwt-auth', 'as' => 'notification.store', 'uses' => 'Notification@update']);
        Route::post('notification/{id}/read', ['before' => 'jwt-auth', 'as' => 'notification.read', 'uses' => 'Notification@update']);
        Route::put('notification/{id}/read', ['before' => 'jwt-auth', 'as' => 'notification.read', 'uses' => 'Notification@update']);
    });
});
