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

Route::get('/admin', 'App\Http\Controllers\AdminController@index');
Route::get('/logout', 'App\Http\Controllers\AdminController@logout');
Route::post('/admin', 'App\Http\Controllers\AdminController@login');
Route::get('/', 'App\Http\Controllers\HomeController@index');
Route::get('/init', 'App\Http\Controllers\ApiController@init')->middleware("json");
Route::get('/{id}', 'App\Http\Controllers\UserController@index');
Route::post('/{id}', 'App\Http\Controllers\UserController@upload');

//BACKEND
Route::get('/{id}/status', 'App\Http\Controllers\ApiController@checkSession')->middleware("json");
Route::get('/{id}/print/{type}', 'App\Http\Controllers\ApiController@print')->middleware("json");