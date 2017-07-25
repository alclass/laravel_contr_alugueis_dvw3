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
    /estate/rent
    ------------
    /estate/rent/contracts
    /estate/rent/contract{id}
    /estate/rent/contract/register
    ------------

*/

Route::prefix('/estate')->group( function() {
  Route::prefix('/rent')->group(   function() {

    // -----------------------------
    // === At /estate/rent/contracts
    // -----------------------------
    Route::prefix('/contracts')->group( function() {
      //===>>> /estate/rent/contracts
      Route::get('/', [
        'as'   => 'contratos.historico',
        'uses' => 'Immeubles\ContractController@history'
      ]);
      //===>>> /estate/rent/contracts/ongoing
      Route::get('/ongoing', [
        'as'   => 'contratos.em.tela',
        'uses' => 'Immeubles\ContractController@index'
      ]);

    }); // ends Route::prefix('/contracts')

    // -----------------------------
    // === At /estate/rent/contract
    // -----------------------------
    Route::prefix('/contract')->group( function() {

      //===>>> /estate/rent/contract/{id}
      Route::get('/{id}', [
      	'as'   => 'contract',
        'uses' => 'Immeubles\ContractController@show'
      ]);
      //===>>> /estate/rent/contract/register
      Route::post('/register', [
        'as'   => 'contrato.cadastrar',
        'uses' => 'Immeubles\ContractController@store'
      ]);
    }); // ends Route::prefix('/contract')
  }); // ends Route::prefix('/rent')
}); // ends Route::prefix('/estate')
