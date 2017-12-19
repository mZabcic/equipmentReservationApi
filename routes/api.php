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
    
        'prefix' => 'auth'
    
    ], function ($router) {
    
        Route::post('/register', 'UserController@register');
        Route::post('/login', 'UserController@login');
    });

Route::group([
    
        'middleware' => 'jwt',
        'prefix' => 'users'
    
    ], function ($router) {
        Route::get('/current', 'UserController@currentUser');
        Route::get('/', 'UserController@getUsers');
        Route::get('/{id}', 'UserController@user');
        Route::put('/edit', 'UserController@edit');
    });
    

Route::group([
        
            'middleware' => 'jwt',
            'prefix' => 'items'
        
        ], function ($router) {
            Route::get('/', 'ItemsController@getAll');
            Route::group([
                
                    'middleware' => 'jwt',
                    'prefix' => 'details'
                
                ], function ($router) {
                    Route::get('/', 'DetailsController@getAll');
                    Route::get('/devicetypes', 'DetailsController@getDeviceTypes');
                    Route::get('/kits', 'DetailsController@getKits');
                    Route::get('/subtypes', 'DetailsController@getSubtypes');
                    Route::get('/types', 'DetailsController@getTypes');
    });
            
});


Route::group([
    
        'middleware' => ['jwt', 'admin'],
        'prefix' => 'admin'
    
    ], function ($router) {
        Route::group([
                'prefix' => 'items'
            
            ], function ($router) {
                Route::post('/create', 'ItemsController@create');
                Route::post('/create/file', 'ItemsController@createFromFile');
});
Route::group([
    'prefix' => 'users'

], function ($router) {
    Route::delete('/delete/{id}', 'UserController@delete');
    
});
        
});


