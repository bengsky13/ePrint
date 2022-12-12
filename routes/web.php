<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/test', 'App\Http\Controllers\ApiController@doPrint');
Route::get('/', 'App\Http\Controllers\HomeController@index');
Route::get('/init', 'App\Http\Controllers\ApiController@init')->middleware("json");
Route::get('/{id}', 'App\Http\Controllers\UserController@index');
Route::post('/{id}', 'App\Http\Controllers\UserController@upload');

//BACKEND
Route::get('/{id}/status', 'App\Http\Controllers\ApiController@checkSession')->middleware("json");
Route::get('/{id}/print/{type}', 'App\Http\Controllers\ApiController@print')->middleware("json");