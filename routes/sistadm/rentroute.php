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
    /sistadm/rent
    ------------
    /sistadm/rent/contracts
    /sistadm/rent/contract{id}
    /sistadm/rent/contract/register
    ------------

*/

Route::middleware('auth')-> group( function() {
  Route::prefix('/sistadm')->group( function() {
    Route::prefix('/rent')->group(   function() {

      // -----------------------------
      // === At /sistadm/rent/contracts
      // -----------------------------
      Route::prefix('/contracts')->group( function() {
        //===>>> /sistadm/rent/contracts
        Route::get('/', [
          'as'   => 'contratos.historico',
          'uses' => 'Immeubles\ContractController@history'
        ]);
        //===>>> /sistadm/rent/contracts/ongoing
        Route::get('/ongoing', [
          'as'   => 'contratos.em.tela',
          'uses' => 'Immeubles\ContractController@index'
        ]);

      }); // ends Route::prefix('/contracts')

      // -----------------------------
      // === At /sistadm/rent/contract
      // -----------------------------
      Route::prefix('/contract')->group( function() {

        //===>>> /sistadm/rent/contract/{id}
        Route::get('/{id}', [
        	'as'   => 'contract',
          'uses' => 'Immeubles\ContractController@show'
        ]);
        //===>>> /sistadm/rent/contract/register
        Route::post('/register', [
          'as'   => 'contrato.cadastrar',
          'uses' => 'Immeubles\ContractController@store'
        ]);
      }); // ends Route::prefix('/contract')
    }); // ends Route::prefix('/rent')
  }); // ends Route::prefix('/sistadm')
}); // ends Route::middleware('auth')
