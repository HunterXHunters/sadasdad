<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*Route::get('/', function () {
    return view('welcome');
});
*/

View::composer('layouts.sideview', 'App\Http\Controllers\HeaderController');
Route::get('/','WelcomeController@index');

Route::get('about', function()
{
	return View::make('about');
});


//Consolidates all the routes from routes folder
foreach (glob(__DIR__ . '/web/*.php') as $route_file)
{
require $route_file;
}



