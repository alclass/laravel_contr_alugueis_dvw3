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

  Here we dedicate to routes below
    ------------
    /
    ------------
    /user/{id}
    /user/{id}/dashboard
    /user/dashboard
    /user/acompanying/{year}/{month}
    /user/{id}/acompanying/{year}/{month}
    /users
    /account
    /account/login
    /account/logout
    /account/signup
    ------------
*/
Route::get('/users', [
  'as' => 'users.route',
  'uses' => 'UserController@listUsers',
  // return view('persons.users', ['users' => $users]);
]);

Route::prefix('/user')->group( function() {

  // -------------------
  // === At ROOT /sa
  // -------------------

  //===>>> /sa/users

  //===>>> /sa/user/{id}
  Route::get('/{id}', [
  	'as' => 'persons.user',
    'uses' => 'UserController@showUser',
  ]);

  //===>>> /sa/dashboard
  Route::get('/dashboard', [
    'as'   => 'persons.dashboard',
    'uses' => 'dashboardController@showdashboard',
  ]);

  //  /user/acompanying
  Route::get('/acompanying', [
    'as'   => 'persons.dashboard',
    'uses' => 'dashboardController@showdashboard',
  ]);

  //  /user/acompanying/{year}/{month}
  Route::get('/acompanying/{year}/{month}', [
    'as'   => 'persons.dashboard',
    'uses' => 'dashboardController@showdashboard',
  ]);


}); // ends Route::prefix('/user') :: //===>>> /sa/user

Route::prefix('/account')->group( function() {

  //===>>> /sa/account/signup
  Route::get('/signup', [
    'as' => 'authusers.signup',
    'uses' => 'UserController@signup_via_httpget',
  ]);

  //===>>> /sa/account/signup
  Route::post('/signup', [
    'as' => 'authusers.signup',
    'uses' => 'UserController@signup_via_httppost',
  ]);

  //===>>> /sa/account/login
  Route::get('/login', [
    'as' => 'login', // authusers.
    'uses' => 'UserController@login_via_httpget',
  ]);

  //===>>> /sa/account/login
  Route::post('/login', [
    'as' => 'login', // authusers.
    'uses' => 'UserController@login_via_httppost',
  ]);

  //===>>> /sa/account/logout
  Route::get('/logout', [
    'as' => 'authusers.logout',
    'uses' => 'UserController@logout_via_httpget',
  ]);

  //===>>> /sa/account/logout
  Route::post('/logout', [
    'as' => 'authusers.logout',
    'uses' => 'UserController@logout_via_httppost',
  ]);

}); // ends Route::prefix('/account') //===>>> /sa/account

// ->middleware('auth');
