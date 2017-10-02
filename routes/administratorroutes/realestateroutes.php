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
    /re
    ------------
    /re/{id}
    /re/immeubles
    /re/condo
    ------------

*/

Route::prefix('/re')->group( function() {

  // -------------------
  // === At ROOT /sa
  // -------------------

  //===>>> /sa/{id}
  Route::get('/{id}', [
  	'as'   => 'imovel.show',
    'uses' => 'Immeubles\ImovelController@show'
  ]);
  //===>>> /sa/immeubles
  Route::get('/immeubles', [
    'as'   => 'imoveis',
    'uses' => 'Immeubles\ImovelController@index'
  ]);

  // -------------------
  // === At /sa/condo
  // -------------------
  Route::prefix('/condo')->group( function() {
    //===>>> /sa/condo/{imovel_id}/fees
    Route::get('/{imovel_id}/fees', [
    	'as'   => 'condominio.tarifas',
      'uses' => 'Immeubles\ImovelController@show_condominium'
    ]);
  }); // ends Route::prefix('/condo')

}); // ends Route::prefix('/sa')


// ->middleware('auth');
