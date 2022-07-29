<?php
Route::group(['before' => 'authenticates', 'middleware' => 'auth.custom'], function()
{
    Route::GET('customer/index', 'CustomerController@index');
    Route::GET('customer/onboard', 'CustomerController@onboard');
    Route::POST('customer/savecustomer', 'CustomerController@saveCustomer');
    Route::GET('customer/getcustomers', 'CustomerController@getCustomers');
    Route::GET('customer/getZones', 'CustomerController@getZones');
    Route::GET('customer/getcities', 'CustomerController@getCities');
    
    Route::get('customer/editcustomer/{customer_id}', 'CustomerController@editCustomer');
    
    Route::get('customer/viewcustomer', 'CustomerController@editCustomer');    
    Route::post('customer/updatecustomer', 'CustomerController@updateCustomer');
    Route::any('customer/approvecustomer/{customer_id}', 'CustomerController@approveCustomer');
    Route::get('customer/getcomponentdata', 'CustomerController@getComponentData');
    Route::post('customer/uploadhandler', 'CustomerController@uploadHandler');
    Route::post('customer/validateemail', 'CustomerController@validateEmail');
    Route::post('customer/addlocationcity', 'CustomerController@addlocationCity');
    Route::any('customer/deletecustomer/{customer_id}', 'CustomerController@deleteCustomer');
    Route::any('customer/restorecustomer/{customer_id}', 'CustomerController@restoreCustomer');
    Route::post('customer/saveerpconfigurations', 'CustomerController@saveErpConfigurations');    
    Route::post('customer/uniquevalidation', 'CustomerController@uniqueValidation');
    Route::post('customer/validatebrandowner', 'CustomerController@validateBrandOwner');
    
    Route::get('customer/locations', 'CustomerController@viewLocations');
    Route::get('customer/getLocations', 'CustomerController@getLocations');
    Route::post('customer/savelocation', 'CustomerController@saveLocation');
    Route::get('customer/editlocation/{location_id}', 'CustomerController@editLocation');
    Route::any('customer/updatelocation/{location_id}', 'CustomerController@updateLocation');

    Route::get('customer/editcustomer/customer/deletelocation/{location_id}', 'CustomerController@deleteLocation');
    Route::get('customer/editcustomer/customer/restorelocation/{location_id}', 'CustomerController@restoreLocation');


    Route::post('customer/savelocationfromerp', 'CustomerController@saveLocationFromErp');
    Route::post('customer/savelocationtypefromexcel', 'CustomerController@saveLocationTypeFromExcel');
    Route::get('customer/getlocationsbytype', 'CustomerController@getLocationsByType');

    Route::get('customer/getTreeLocation/{manufacturer_id}','CustomerController@getTreeLocations');
    Route::get('customer/locationtypes', 'CustomerController@viewLocationTypes');
    Route::get('customer/getlocationtype', 'CustomerController@getLocationTypes');
    Route::post('customer/savelocationtype', 'CustomerController@saveLocationType');
    Route::get('customer/editlocationtype/{location_id}', 'CustomerController@editLocationType');
    Route::any('customer/updatelocationtype/{location_type_id}', 'CustomerController@updateLocationType');    
    Route::any('customer/deletecustomer/{customer_id}', 'CustomerController@deleteCustomer');
    Route::any('customer/restorecustomer/{customer_id}', 'CustomerController@restoreCustomer');
    
    Route::post('customer/editcustomer/customer/deletelocationtype/{location_type_id}', 'CustomerController@deleteLocationType');
    Route::get('customer/editcustomer/customer/restorelocationtype/{location_type_id}', 'CustomerController@restoreLocationType');
    
    Route::post('customer/savebusinessunit', 'CustomerController@saveBusinessUnit');
    
    Route::get('customer/gettransaction/{id}', 'CustomerController@getTransaction');
    Route::post('customer/savetransaction', 'CustomerController@saveTransaction');
    Route::get('customer/edittransaction/{id}', 'CustomerController@editTransaction');
    Route::put('customer/updatetransaction/{id}', 'CustomerController@updateTransaction');
    Route::get('customer/deletetransaction/{id}', 'CustomerController@deleteTransaction');
});
Route::get('customer/testmail', 'CustomerController@testMail');
Route::post('customer/validateotp', 'CustomerController@validateOtp');
Route::post('customer/sendotp', 'CustomerController@sendOtp');
Route::get('customer/confirmation/token/{token}', 'CustomerController@confirmationForm');
Route::post('customer/confirmcustomer', 'CustomerController@updateCustomer');
Route::get('customers/error', function()
{
    return View::make('customers.error');
});
Route::get('customer/download/{type}', 'CustomerController@getDownload');
Route::any('customer/erpuniquevalidation','CustomerController@erpuniquevalidation');

Route::get('customer/exportTo/{type}', 'CustomerController@exportTo');