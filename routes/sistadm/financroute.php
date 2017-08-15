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
    /financ
    ------------
    /financ/evolucao
    ------------

*/
Route::prefix('/financ')->group( function() {

  // -------------------
  // === At ROOT /financ
  // -------------------

  // financiamentopagamentos
  Route::get('/evolucao/{borrower_id}', [
    'as' => 'financevolucao',
    'uses' => 'Finance\AmortizationPaymentController@show', //_parcel_evolver_report'
  ]);

}); // ends Route::prefix('/financ')
// ->middleware('auth');
