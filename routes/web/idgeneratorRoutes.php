<?php
Route::resource('idgenerator/generate','IdgeneratorController@generate');
Route::resource('idgenerator/bankcheck','IdgeneratorController@bankcheck');
Route::post('idgenerator/generateIot','IdgeneratorController@generateIot');