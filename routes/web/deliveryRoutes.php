<?php
//views
Route::get('delivery/createStoEcc', 'DeliveryController@createStoEcc');
Route::get('deliveries/getPdfDetailsForTp/{docNumber}', 'DeliveryController@getPdfDetailsForTp');
Route::get('delivery/getElementdata/{arg}/{id}', 'DeliveryController@getElementdata');
Route::get('deliveries/downloadTPPDF/{docNumber}', 'DeliveryController@TPPdfDownload');
Route::group(['middleware' => 'auth.custom'], function()
{
Route::get('deliveries/add','DeliveryController@add');
Route::post('delivery/save','DeliveryController@save');
//views end

//Route::get('delivery/getElementdataNew', 'DeliveryController@getElementdata');

Route::post('delivery/createDeliveryOrders', 'DeliveryController@createDeliveryOrders');

Route::get('delivery/delwithProduct/{Id}/{product_id}', 'DeliveryController@delwithProduct');

Route::get('delivery/editDeliveryDetails/{delivery_id}/{product_id}', 'DeliveryController@editDeliveryDetails');

Route::put('delivery/updateDeliveryDetails/{delivery_id}/{product_id}', 'DeliveryController@updateDeliveryDetails');

Route::post('delivery/savedeliveryfromexcel', 'DeliveryController@savedeliveryfromexcel');

//Route::get('delivery/{arg}','DeliveryController@index');
Route::any('delivery/getDeliveries/{id}', 'DeliveryController@getDeliveries');

Route::get('delivery/getElement	/{id}', 'DeliveryController@getElementdata');
Route::get('delivery/getElementdata/{arg}', 'DeliveryController@getElementdata');

Route::get('delivery/{arg}','DeliveryController@index');
Route::get('getPlantStorageLoc/{plantCode}','DeliveryController@getPlantStrLocation');
Route::get('getConversion/{qty}/{UOM}/{p_id}','DeliveryController@getConversion');
});