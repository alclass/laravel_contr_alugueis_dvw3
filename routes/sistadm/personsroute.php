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
    /sistadm
    ------------
    /sistadm/user/{id}
    /sistadm/users
    /sistadm/persons
    /sistadm/account
    /sistadm/account/login
    /sistadm/account/logout
    /sistadm/account/signup
    ------------

*/
Route::prefix('/sistadm')->group( function() {

  // -------------------
  // === At ROOT /sistadm
  // -------------------

  //===>>> /sistadm/users
  Route::get('/users', [
    'as' => 'users.route',
    'uses' => 'UserController@listUsers',
  	// return view('persons.users', ['users' => $users]);
  ]);

  //===>>> /sistadm/user/{id}
  Route::get('/user/{id}', [
  	'as' => 'persons.user',
    'uses' => 'UserController@showUser',
  ]);

  //===>>> /sistadm/persons
  Route::prefix('/persons')->group( function() {

    //===>>> /sistadm/persons
    Route::get('/', [
      'as' => 'persons.users',
      'uses' => 'PersonController@listPersons',
    ]);

    Route::get('/userdashboard', [
      'as'   => 'persons.userdashboard',
      'uses' => 'UserDashboardController@showUserDashboard',
    ]);

  }); // ends Route::prefix('/persons') :: //===>>> /sistadm/persons

  Route::prefix('/account')->group( function() {

    //===>>> /sistadm/account/signup
    Route::get('/signup', [
      'as' => 'authusers.signup',
      'uses' => 'UserController@signup_via_httpget',
    ]);

    //===>>> /sistadm/account/signup
    Route::post('/signup', [
      'as' => 'authusers.signup',
      'uses' => 'UserController@signup_via_httppost',
    ]);

    //===>>> /sistadm/account/login
    Route::get('/login', [
      'as' => 'login', // authusers.
      'uses' => 'UserController@login_via_httpget',
    ]);

    //===>>> /sistadm/account/login
    Route::post('/login', [
      'as' => 'login', // authusers.
      'uses' => 'UserController@login_via_httppost',
    ]);

    //===>>> /sistadm/account/logout
    Route::get('/logout', [
      'as' => 'authusers.logout',
      'uses' => 'UserController@logout_via_httpget',
    ]);

    //===>>> /sistadm/account/logout
    Route::post('/logout', [
      'as' => 'authusers.logout',
      'uses' => 'UserController@logout_via_httppost',
    ]);

  }); // ends Route::prefix('/account') //===>>> /sistadm/account

}); // ends Route::prefix('/sistadm')
// ->middleware('auth');
