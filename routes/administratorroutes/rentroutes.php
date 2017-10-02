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
    /sa/rent
    ------------
    /sa/rent/contracts
    /sa/rent/contract{id}
    /sa/rent/contract/register
    ------------

*/

Route::middleware('auth')-> group( function() {
  Route::prefix('/sa')->group( function() {
    Route::prefix('/rent')->group(   function() {

      // -----------------------------
      // === At /sa/rent/contracts
      // -----------------------------
      Route::prefix('/contracts')->group( function() {
        //===>>> /sa/rent/contracts
        Route::get('/', [
          'as'   => 'contratos.historico',
          'uses' => 'Immeubles\ContractController@history'
        ]);
        //===>>> /sa/rent/contracts/ongoing
        Route::get('/ongoing', [
          'as'   => 'contratos.em.tela',
          'uses' => 'Immeubles\ContractController@index'
        ]);

      }); // ends Route::prefix('/contracts')

      // -----------------------------
      // === At /sa/rent/contract
      // -----------------------------
      Route::prefix('/contract')->group( function() {

        //===>>> /sa/rent/contract/{id}
        Route::get('/{id}', [
        	'as'   => 'contract',
          'uses' => 'Immeubles\ContractController@show'
        ]);
        //===>>> /sa/rent/contract/register
        Route::post('/register', [
          'as'   => 'contrato.cadastrar',
          'uses' => 'Immeubles\ContractController@store'
        ]);
      }); // ends Route::prefix('/contract')
    }); // ends Route::prefix('/rent')
  }); // ends Route::prefix('/sa')
}); // ends Route::middleware('auth')
