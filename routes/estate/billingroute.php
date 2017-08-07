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
  ---------------
  /estate/billing
  ---------------
  /estate/billings
    /estate/billings/ouverts
    /estate/billings/reconciled
  /estate/billing
    /estate/billing/show/{cobranca_id}
    /estate/billing/showbyref/{contract_id}/{year}/{month}
    /estate/billing/edit/{contract_id}/{year}/{month}
    /estate/billing/edit

    /estate/billing/verify/{contract_id}/{year}/{month}
    /estate/billing/createdynamic/{contract_id}/{year}/{month}

  /estate/billing/items
    /estate/billing/items/maintain/{contract_id}/{year}/{month}


  /estate/billing/payments
    /estate/billing/payments/{contract_id}
    /estate/billing/payments/ouverts
  /estate/billing/payment
  /estate/billing/payment/{contract_id}/{year}/{month}
  /estate/billing/payment/reconcile
    /estate/billing/payment/reconcile/history
    /estate/billing/payment/reconcile/{contract_id}/{year}/{month}
    /estate/billing/payment/reconcile
  /estate/billing/late
    /estate/billing/late/all
    /estate/billing/late/{contract_id}
    /estate/billing/late/{contract_id}/calcfinanctimecorrection
  ------------
*/
Route::prefix('/estate')->group( function() {

  // -----------------------
  // === At /estate/billings
  // -----------------------
  Route::prefix('/billings')->group( function() {

    //===>>> estate/billings/ouverts
    Route::get('/ouverts', [
      'as'   => 'cobrancas.abertas',
      'uses' => 'Billing\CobrancaController@abertas'
    ]);
    //===>>> estate/billings/onref/{year?}/{month?}
    Route::get('/onref/{year?}/{month?}', [
      'as'   => 'cobrancas.onref',
      'uses' => 'Billing\CobrancaController@onref'
    ]);
    //===>>> estate/billings/onref/{year?}/{month?}
    Route::get('/onlyrent/onref/{year?}/{month?}', [
      'as'   => 'cobrancas.onlyrent.onref',
      'uses' => 'Billing\CobrancaController@onlyrent_onref'
    ]);
    //===>>> estate/billings/reconciled
    Route::get('reconciled', [
      'as'   => 'cobrancas.conciliadas',
      'uses' => 'Billing\CobrancaController@conciliadas'
    ]);
  }); // ends Route::prefix('/billings') //===>>> estate/billings

  // ----------------------
  // === At /estate/billing
  // ----------------------
  Route::prefix('/billing')->group( function() {

    //===>>> estate/billing/show/{cobranca_id}
    Route::get('show/{cobranca_id}', [
      'as'   => 'cobranca.mostrar',
      'uses' => 'Billing\CobrancaController@show'
    ]);

    //===>>> estate/billing/showviaref/{contract_id}/{year}/{month}
    Route::get('showviaref/{contract_id}/{year}/{month}', [
      'as'   => 'cobranca.mostrarviaref',
      'uses' => 'Billing\CobrancaController@showviaref'
    ]);

    //===>>> estate/billing/edit/{contract_id}/{year}/{month} GET
    Route::get('edit/{contract_id}/{year}/{month}', [
      'as'=>'cobranca.mensal.editar',
      'uses'=>'Billing\CobrancaController@edit_via_httpget'
    ]);
    //===>>> estate/billing/edit POST
    Route::post('/edit', [
      'as'=>'cobranca.mensal.editar',
      'uses'=>'Billing\CobrancaController@edit_via_httppost'
    ]);

    //===>>> estate/billing/analyzeverify/{contract_id}/{year}/{month} GET
    Route::get('analyzeverify/{cobranca_id}', [
      'as'=>'cobranca.mensal.analisarverificar',
      'uses'=>'Billing\CobrancaController@analyzeverify'
    ]);


    //===>>> estate/billing/createdynamic/{contract_id}/{year}/{month} GET
    Route::get('createdynamic/{contract_id}/{year}/{month}', [
      'as'=>'cobranca.mensal.criardinamicamente',
      'uses'=>'Billing\CobrancaController@createdynamic'
    ]);
    //===>>> estate/billing/createdynamic POST
    Route::post('/createdynamic', [
      'as'=>'cobranca.mensal.criardinamicamente',
      'uses'=>'Billing\CobrancaController@createdynamic'
    ]);


    // -------------------------------
    // === At /estate/billing/payments
    // -------------------------------
    Route::prefix('/payments')->group( function() {
      //===>>> estate/billing/payments
      Route::get('/', [
        'as'   => 'pagtos.contrato.historico',
        'uses' => 'Billing\PaymentController@history',
      ]);
      //===>>> estate/billing/payments/{contract_id}
      Route::get('/payments/{contract_id}', [
        'as'   => 'pagtos.contrato.historico',
        'uses' => 'Billing\PaymentController@list_per_contract',
      ]);
      //===>>> estate/billing/payments/ouverts
      Route::get('/ouverts', [
        'as'   => 'pagtos.abertos',
        'uses' => 'Billing\PaymentController@abertos',
      ]);
    }); // ends Route::prefix('/payments') //===>>> estate/billing/payments

    // ------------------------------
    // === At /estate/billing/payment
    // ------------------------------
    Route::prefix('/payment')->group( function() {

      //===>>> estate/billing/payment/{contract_id}/{year}/{month}
      Route::get('/{contract_id}/{year}/{month}', [
        'as'   => 'pagto',
        'uses' => 'Billing\PaymentController@listar_aberto'
      ]);

      // ----------------------------------------
      // === At /estate/billing/payment/reconcile
      // ----------------------------------------
      Route::prefix('/reconcile')->group( function() {

        //===>>> estate/billing/payment/reconcile/history
        Route::get('/history', [
          'as'   => 'conciliacao.historico',
          'uses' => 'Billing\PaymentController@reconcilehistory'
        ]);
        //===>>> estate/billing/payment/reconcile/{contract_id}/{year}/{month} GET
        Route::get('/{contract_id}/{year}/{month}', [
          'as'   => 'pagto.conciliar',
          'uses' => 'Billing\PaymentController@reconcile'
        ]);
        //===>>> estate/billing/payment/reconcile/{contract_id}/{year}/{month} POST
        Route::post('/reconcile', [     //{contract_id}/{year}/{month}
          'as'   => 'pagto.conciliar',
          'uses' => 'Billing\PaymentController@reconcile'
        ]);
      }); // ends Route::prefix('/reconcile') //===>>> estate/billing/payment/reconcile
    }); // ends Route::prefix('/payment') //===>>> estate/billing/payment

    // ---------------------------
    // === At /estate/billing/late
    // ---------------------------
    Route::prefix('/late')->group( function() {

      //===>>> /estate/billing/late/all
      Route::get('/all', [
        'as'   => 'cobrancas.emmora',
        'uses' => 'Billing\MoraDebitoController@listall'
      ]);
      //===>>> /estate/billing/late/{$contract_id}
      Route::get('/{contract_id}', [
      	'as'   => 'cobrancas.emmora.contrato',
        'uses' => 'Billing\MoraDebitoController@list'
      ]);
      //===>>> /estate/billing/late/{contract_id}/calcfinanctimecorrection
      Route::get('/{contract_id}/calcfinanctimecorrection', [
      	'as'   => 'cobrancas.emmora.exibirmoradebito',
        'uses' => 'Billing\MoraDebitoController@showcalcfinanctimecorrection'
      ]);
    }); // ends Route::prefix('/late')     //===>>> /estate/billing/late
  }); // ends Route::prefix('/billing') //===>>> /estate/billing
}); // ends Route::prefix('/estate') //===>>> /estate
