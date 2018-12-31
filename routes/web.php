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

Route::get('/', function () {
    return view('welcome');
});


Route::post('/getResults', [
    'uses' => 'ResultController@getResults',
    'as' => 'getResults'
]);

Route::post('/getPaginatedResults', [
    'uses' => 'ResultController@getPaginatedResults',
    'as' => 'getPaginatedResults'
]);

Route::post('/getRecipe', [
    'uses' => 'RecipeController@getRecipe',
    'as' => 'getRecipe'
]);

Route::post('/getList', [
    'uses' => 'ListController@getList',
    'as' => 'getList'
]);
