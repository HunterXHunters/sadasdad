<?php
Route::post('scoapi/{api_name}','ScoapiController@checkUserPermission');
Route::get('scoapi/{api_name}','TrackandtraceController@checkUserPermission');
Route::post('grnCreation', 'ScoapiController@grnCreation');
Route::post('grnCancellation', 'ScoapiController@grnCancellation');
Route::post('grnCreation-test', 'TestapiController@grnCreation');

Route::group(['middleware' => 'auth.custom'], function() {
Route::post('scoapi_test/{api_name}','ScoapiController_test@checkUserPermission');
Route::post('scoapi_test2/{api_name}','ScoapiController_test2@checkUserPermission');
Route::post('scoapi_cron/confirmProductionOrderEcc','ScoapiController@confirmProductionOrderEcc');
});