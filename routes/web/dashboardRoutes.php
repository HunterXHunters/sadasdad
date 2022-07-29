<?php 
Route::group(['middleware' => 'auth.custom'], function() {
    Route::any('dashboard/getProducts','DashboardController@getProducts');
    Route::any('dashboard/getLocations','DashboardController@getLocations');
    Route::any('dashboard/getStorageLocations','DashboardController@getStorageLocations');
});