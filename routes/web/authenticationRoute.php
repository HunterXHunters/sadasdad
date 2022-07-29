<?php
Route::get('login','AuthenticationController@index')->name('login');
Route::get('authorize/check','AuthenticationController@authorizeCheck');
Route::get('login/authorize/grant','AuthenticationController@authorizeGrant');
Route::get('oauth/authorize/','AuthenticationController@loginViaOauth');
Route::put('login/authorize','AuthenticationController@checkAuth')->middleware('AfterMiddleware');;
Route::get('logout','AuthenticationController@logout');
Route::get('authorize/abort','AuthenticationController@logout');
Route::post('forgot','AuthenticationController@forgot');
Route::get('password/reset/{token}','AuthenticationController@reset');
Route::any('passwordreset','AuthenticationController@passwordreset');


Route::get('users/download/bulkupdatetemplate','AuthenticationController@downloadbulkupdatetemplate');
Route::post('users/saveusersfromexcel','AuthenticationController@saveUsersFromExcel');
//add by ruchita//
   Route::group(['before'=>'authenticates'],function(){
    Route::get('users','AuthenticationController@users');
    Route::get('users/add','AuthenticationController@add_User');
    Route::get('users/edit/{user_id}','AuthenticationController@edit_User');
    Route::get('users/delete/{user_id}','AuthenticationController@delete_User');
    Route::get('users/usersList','AuthenticationController@usersList');
    Route::post('users/save/{user_id}','AuthenticationController@saveUser');
    Route::post('users/uploadProfilePic','AuthenticationController@uploadProfilePic');

});
//changes route by boorla
Route::get('users/exportuserdata','AuthenticationController@exportdatausers');

//app version routes

Route::get('appVersion','AppVersionController@index');
Route::get('appVersion/show','AppVersionController@show');
Route::get('appVersion/create','AppVersionController@create');
Route::post('appVersion/store','AppVersionController@store');
Route::get('appVersion/edit/{id}','AppVersionController@edit');
Route::put('appVersion/update/{id}','AppVersionController@update');
Route::any('appVersion/delete/{id}','AppVersionController@delete');