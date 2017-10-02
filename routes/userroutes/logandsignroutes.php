<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------

  This Laravel routing file has been registered in
    ------------------------------------
    RouteServiceProvider::mapWebRoutes()
    ------------------------------------
    in app\Providers\RouteServiceProvider.php

  These are the following routes:

  /signup
  /login
  /logout
  
*/

Route::get('/signup', [
  'as' => 'signuproute',
  'uses' => 'UserController@signup_via_httpget',
]);

Route::post('/signup', [
  'as' => 'signuproute',
  'uses' => 'UserController@signup_via_httppost',
]);

Route::get('/login', [
  'as' => 'loginroute', // authusers.
  'uses' => 'UserController@login_via_httpget',
]);

Route::post('/login', [
  'as' => 'loginroute', // authusers.
  'uses' => 'UserController@login_via_httppost',
]);


Route::middleware('auth')->group( function() {

  Route::get('/logout', [
    'as' => 'logoutroute',
    'uses' => 'UserController@logout_via_httpget',
  ]);

  Route::post('/logout', [
    'as' => 'logoutroute',
    'uses' => 'UserController@logout_via_httppost',
  ]);

} // ->middleware('auth');
