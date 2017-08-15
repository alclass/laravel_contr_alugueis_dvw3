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
    /sistadm/{id}
    /sistadm/immeubles
    /sistadm/condo
    ------------

*/

Route::prefix('/sistadm')->group( function() {

  // -------------------
  // === At ROOT /sistadm
  // -------------------

  //===>>> /sistadm/{id}
  Route::get('/{id}', [
  	'as'   => 'imovel.show',
    'uses' => 'Immeubles\ImovelController@show'
  ]);
  //===>>> /sistadm/immeubles
  Route::get('/immeubles', [
    'as'   => 'imoveis',
    'uses' => 'Immeubles\ImovelController@index'
  ]);

  Route::get('/dashboard', [
    'as' => 'dashboard',
    'uses' => 'Immeubles\ContractController@dashboard',
  ]);

  Route::get('/dashboard/{user_id}', [
    'as' => 'user.dashboard',
    'uses' => 'Immeubles\ContractController@userdashboard',
  ]);

  // -------------------
  // === At /sistadm/condo
  // -------------------
  Route::prefix('/condo')->group( function() {
    //===>>> /sistadm/condo/{imovel_id}/fees
    Route::get('/{imovel_id}/fees', [
    	'as'   => 'condominio.tarifas',
      'uses' => 'Immeubles\ImovelController@show_condominium'
    ]);
  }); // ends Route::prefix('/condo')

}); // ends Route::prefix('/sistadm')


// ->middleware('auth');
