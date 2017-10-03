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
    ------------
*/

Route::middleware('auth')->group( function() {
  Route::get('/acompanhamento', [
    'as' => 'acompanhamentoroute',
    'uses' => 'CobrancaController@generateAcompanhamento',
  ]);

  Route::get('/acompanhamento/{year}/{month}', [
    'as' => 'acompanhamentomensalroute',
    'uses' => 'CobrancaController@generateAcompanhamento',
  ]);

  Route::get('/dashboard', [
    'as' => 'dashboardroute',
    'uses' => 'UserController@generateDashboard',
  ]);

  Route::get('/historico', [
    'as' => 'historicoroute',
    'uses' => 'CobrancaController@generateHistoricoDasCobrancas',
  ]);

  Route::prefix('/encargos')->group( function() {
    //===>>> //encargos/iptu
    Route::get('iptu/', [
    	'as' => 'repasseipturoute',
      'uses' => 'RepasseController@showIptuRepasses',
    ]);
    //===>>> //encargos/condominio
    Route::get('condominio/', [
    	'as' => 'repassecondominioroute',
      'uses' => 'RepasseController@showCondominioRepasses',
    ]);
  }); // ends Route::prefix('/encargos') :: //===>>> /sa/user
  Route::get('/userconfig', [
    'as'   => 'userconfigroute',
    'uses' => 'UserController@showUserConfig',
  ]);
  Route::post('/userconfig', [
    'as'   => 'userconfigroute',
    'uses' => 'UserController@editUserConfig',
  ]);
}); // ->middleware('auth');
