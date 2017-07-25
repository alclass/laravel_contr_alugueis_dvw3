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
    /estate
    ------------
    /estate/{id}
    /estate/immeubles
    /estate/condo
    ------------

*/

Route::prefix('/estate')->group( function() {

  // -------------------
  // === At ROOT /estate
  // -------------------

  //===>>> /estate/{id}
  Route::get('/{id}', [
  	'as'   => 'imovel.show',
    'uses' => 'Immeubles\ImovelController@show'
  ]);
  //===>>> /estate/immeubles
  Route::get('/immeubles', [
    'as'   => 'imoveis',
    'uses' => 'Immeubles\ImovelController@index'
  ]);

  // -------------------
  // === At /estate/condo
  // -------------------
  Route::prefix('/condo')->group( function() {
    //===>>> /estate/condo/{imovel_id}/fees
    Route::get('/{imovel_id}/fees', [
    	'as'   => 'condominio.tarifas',
      'uses' => 'Immeubles\ImovelController@show_condominium'
    ]);
  }); // ends Route::prefix('/condo')

}); // ends Route::prefix('/estate')
