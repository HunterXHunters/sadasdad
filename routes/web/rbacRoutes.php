<?php
Route::get('rbac/exportusers','RbacController@exportUsers');
Route::get('rbac/featuresexport','RbacController@featuresExport');

Route::get('rbac/exportrolesandfeatures','RbacController@ExportrolesAndFeatures');
Route::group(['prefix' => 'rbac', 'before'=>'authenticates', 'middleware' => 'auth.custom'],function(){
    Route::get('/','RbacController@index');
    Route::get('getRoles','RbacController@getRoles');
    Route::get('add','RbacController@create');
    Route::put('saveRole/{role_id}','RbacController@saveRole');
    Route::get('edit/{role_id}','RbacController@edit');
    Route::get('delete/{role_id}','RbacController@delete');
    Route::get('getUserDetail/{user_id}','RbacController@getUser');
    Route::get('getChild/{feature_id}','RbacController@getChild');
    Route::put('saveUser','RbacController@saveUser');
    Route::post('getRoleforInherit/{role_id}','RbacController@getRoleforInherit');
    Route::get('features','RbacController@features');
    Route::get('getdata','RbacController@getdata');
    Route::put('update/{feature_id}','RbacController@update');
    Route::post('store','RbacController@store');
    Route::get('editfeature/{feature_id}','RbacController@editFeature');
    Route::post('deletefeature/{feature_id}','RbacController@destroy');
    Route::post('deleteParentfeature/{feature_id}','RbacController@FeatureDelete');
    Route::post('uploadProfilePic','RbacController@uploadProfilePic');
});
