<?php

use Illuminate\Http\Request;

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



Route::group([
    
        'middleware' => 'api',
        'prefix' => 'users'
    
    ], function ($router) {
    
        Route::get('/current', 'UserController@currentUser');
        Route::post('/register', 'UserController@register');
        Route::get('/', 'UserController@getUsers');
        Route::post('/login', 'UserController@login');
    });
    

    Route::group([
        
            'middleware' => 'api',
            'prefix' => 'items'
        
        ], function ($router) {
            Route::get('/', 'ItemsController@getAll');
            
        });