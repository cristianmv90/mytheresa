<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('products')->name('products.')->group(function () {
    Route::get('list', 'App\Http\Controllers\API\ProductController@index')->name('list');
    Route::post('store', 'App\Http\Controllers\API\ProductController@store')->name('store');
    Route::put('update/{sku}', 'App\Http\Controllers\API\ProductController@update')->name('update');
    Route::get('show/{sku}', 'App\Http\Controllers\API\ProductController@show')->name('show');
    Route::delete('destroy/{sku}', 'App\Http\Controllers\API\ProductController@destroy')->name('destroy');
});