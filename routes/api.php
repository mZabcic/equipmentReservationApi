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
        Route::delete('/delete', 'UserController@deleteMe');
    });
    

Route::group([
        
            'middleware' => 'jwt',
            'prefix' => 'items'
        
        ], function ($router) {
            Route::get('/', 'ItemsController@getAll');
            Route::get('/status/{id}', 'ItemsController@getStatus');
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
                Route::put('/edit', 'ItemsController@edit');
                Route::delete('/delete/{id}', 'ItemsController@delete');
                Route::get('/', 'ItemsController@getAll');
                Route::put('/working/{id}', 'ItemsController@working');
                Route::put('/broken/{id}', 'ItemsController@notWorking');

                Route::group([
                    'prefix' => 'details'
                
                ], function ($router) {
                    Route::delete('devicetypes/delete/{id}', 'DetailsController@deleteDeviceType');
                    Route::delete('types/delete/{id}', 'DetailsController@deleteType');
                    Route::delete('subtypes/delete/{id}', 'DetailsController@deleteSubtype');
                    Route::delete('kits/delete/{id}', 'DetailsController@deleteKit');
                    Route::post('devicetypes/create', 'DetailsController@createDeviceType');
                    Route::post('types/create', 'DetailsController@createType');
                    Route::post('subtypes/create', 'DetailsController@createSubType');
                    Route::post('kits/create', 'DetailsController@createKit');
                    Route::get('/', 'DetailsController@getAll');
                    Route::get('/devicetypes', 'DetailsController@getDeviceTypes');
                    Route::get('/kits', 'DetailsController@getKits');
                    Route::get('/subtypes', 'DetailsController@getSubtypes');
                    Route::get('/types', 'DetailsController@getTypes');
                });
                        
});
Route::group([
    'prefix' => 'users'

], function ($router) {
    Route::delete('/delete/{id}', 'UserController@delete');
    Route::put('/edit', 'UserController@editAdmin');
    Route::get('/current', 'UserController@currentUser');
    Route::get('/', 'UserController@getUsers');
    Route::get('/{id}', 'UserController@user');
    Route::put('/edit/status/{id}', 'UserController@editActive');
    Route::delete('/delete', 'UserController@deleteMe');
});

Route::group([
    'prefix' => 'reservations'

], function ($router) {
    Route::post('/approve', 'ReservationsController@approve');
    Route::post('/return', 'ReservationsController@returned');
    Route::post('/decline', 'ReservationsController@declined');
    Route::post('/extend', 'ReservationsController@extend');
    Route::get('/extends', 'ReservationsController@allExtends');
    Route::get('/', 'ReservationsController@all');
    Route::post('/request', 'ReservationsController@reservationRequest');
    Route::delete('/delete/{id}', 'ReservationsController@deleteAdmin');
    Route::delete('/extend/delete/{id}', 'ReservationsController@deleteExtend');
});
        
});



Route::group([
    
        'middleware' => 'jwt',
        'prefix' => 'reservations'
    
    ], function ($router) {
        Route::get('/', 'ReservationsController@all');
        Route::post('/request', 'ReservationsController@reservationRequest');
        Route::post('/extend', 'ReservationsController@extendRequest');
        Route::post('/delete/{id}', 'ReservationsController@delete');
        Route::get('/user/{id}', 'ReservationsController@byUser');
        Route::get('/item/{id}', 'ReservationsController@byItem');
        Route::get('/extends', 'ReservationsController@myExtends');
        
        Route::group([
            
                'middleware' => 'jwt',
                'prefix' => 'details'
            
            ], function ($router) {
                Route::get('/', 'DetailsController@getStatuses');
             
            });
    });
    


