<?php
Route::group(['middleware' => 'auth.custom'], function() {
Route::any('products', 'ProductController@index');
Route::any('products/index', 'ProductController@index');
Route::any('products/create', 'ProductController@create');
Route::any('products/create1', 'ProductController@create1');
Route::post('products/save', 'ProductController@saveProduct');
Route::post('products/saveattributeset', 'ProductController@saveAttributeSet');
Route::post('products/saveattribute', 'ProductController@saveAttribute');

Route::get('products/deleteproduct/{product_id}', 'ProductController@deleteProduct');
Route::post('products/saveProductsFromExcel', 'ProductController@saveProductsFromExcel');


Route::get('products/exportToproducts/{type}','ProductController@exportToproducts');

Route::get('products/getproducts', 'ProductController@getProducts');

Route::post('products/editsave', 'ProductController@editSaveProduct');
Route::get('products/editproduct/{product_id}', 'ProductController@editProduct');
Route::post('/products/erp_code_uniquevalidation','ProductController@erpCodeUniquevalidation');


Route::post('products/bulkupdateproducts', 'ProductController@bulkUpdateProducts');
Route::get('products/editgdsproduct/{product_id}', 'ProductController@editGdsProduct');
Route::any('product/gdsindex', 'ProductController@gdsIndex');
Route::any('products/gdsCreate', 'ProductController@gdsCreate');
//routes for product location mapping//
Route::post('product/getelementdata', 'ProductController@getElementData');
Route::any('products/getproduct_loactionmapping', 'ProductController@getproduct_loactionmapping');
Route::any('products/product_location_mapping', 'ProductController@product_loc_maping');

Route::post('products/getProductLocMaping', 'ProductController@getProductLocMaping');

//
Route::any('products/deleteFromgrid', 'ProductController@DeletefromGrid');
Route::post('products/uploadhandler', 'ProductController@uploadHandler');



//location routes//
Route::get('products/location', 'ProductController@editCustomer');
    Route::post('products/savelocation', 'ProductController@saveLocation');
        // Route::post('products/savelocation', 'ProductController@saveLocation');


 //location grid//   
Route::get('products/locations', 'ProductController@editCustomer');    
        Route::post('prodcuts/savelocationtype', 'ProductController@saveLocationType');

Route::post('products/savelocationtypefromexcel', 'ProductController@saveLocationTypeFromExcel');
Route::get('products/getlocationsbytype', 'ProductController@getLocationsByType');

    Route::get('products/editlocation/{location_id}', 'ProductController@editLocation');
    Route::get('products/editlocationtype/{location_id}', 'ProductController@editLocationType');
    Route::post('products/updatelocationType/{location_id}', 'ProductController@updateLocationType');

Route::post('products/location/products/deletelocationtype/{location_type_id}','ProductController@deleteLocationType');
Route::any('products/location/products/restorelocationtype/{location_type_id}', 'ProductController@restoreLocationType');
Route::get('products/location/products/deletelocation/{location_id}', 'ProductController@deleteLocation');
    Route::get('products/location/products/restorelocation/{location_id}', 'ProductController@restoreLocation');
    Route::any('products/updatelocation/{loc_id}', 'ProductController@updateLocation');
    Route::get('products/download/{type}', 'ProductController@getDownload');
    Route::get('products/exportTo/{type}', 'ProductController@exportTo');
Route::get('products/getTreeLocation/{manufacturer_id}','ProductController@getTreeLocations');

    Route::post('products/uniquevalidation', 'ProductController@uniqueValidation');

    



//attributes//

    Route::get('product/attributes', 'ProductController@attributes');
    Route::get('products/transaction/{customer_id}', 'ProductController@editTransMain');

    Route::get('products/gettransaction/{id}', 'ProductController@getTransaction');
    Route::post('products/savetransaction', 'ProductController@saveTransaction');
    Route::get('products/edittransaction/{id}', 'ProductController@editTransaction');
    Route::put('products/updatetransaction/{id}', 'ProductController@updateTransaction');
    Route::get('products/deletetransaction/{id}', 'ProductController@deleteTransaction');


Route::post('products/mapProduct','ProductController@mapProduct');
});