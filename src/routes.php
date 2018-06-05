<?php

Route::group(['prefix' => 'logs', 'middleware' => config('log.middleware', [])], function () {
    Route::get('/', '\KemerovoMan\LogVendor\LogController@index');
    Route::get('/{file}', '\KemerovoMan\LogVendor\LogController@show');
});