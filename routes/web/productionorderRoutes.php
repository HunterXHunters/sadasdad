<?php 
Route::group(['middleware' => 'auth.custom'], function() {
Route::any('production_orders','ProductionOrderController@getOrders')->name('profile');;
Route::any('productorder/getPOorders/{p_id}/{l_id}', 'ProductionOrderController@getPOorders');

Route::any('productorder/createOrder', 'ProductionOrderController@createOrder');
Route::any('productorder/getPoQuantity', 'ProductionOrderController@getPoQuantity');

Route::any('productorder/getPOconfirmdetails/{erp_doc_no}/{eseal_doc_no}', 'ProductionOrderController@getPOconfirmdetails');
Route::post('productorder/cancelOrder', 'ProductionOrderController@cancelOrder');

Route::post('productorder/getECCstatus', 'ProductionOrderController@getECCstatus');
Route::post('productorder/getConversion/{qty}/{UOM}/{p_id}', 'ProductionOrderController@getConversion');
});