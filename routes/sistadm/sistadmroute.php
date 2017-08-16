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
