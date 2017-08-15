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
  /sistadm/billing
  ---------------
  /sistadm/billings
    /sistadm/billings/ouverts
    /sistadm/billings/reconciled
  /sistadm/billing
    /sistadm/billing/show/{cobranca_id}
    /sistadm/billing/showbyref/{contract_id}/{year}/{month}
    /sistadm/billing/edit/{contract_id}/{year}/{month}
    /sistadm/billing/edit

    /sistadm/billing/verify/{contract_id}/{year}/{month}
    /sistadm/billing/createdynamic/{contract_id}/{year}/{month}

  /sistadm/billing/items
    /sistadm/billing/items/maintain/{contract_id}/{year}/{month}


  /sistadm/billing/payments
    /sistadm/billing/payments/{contract_id}
    /sistadm/billing/payments/ouverts
  /sistadm/billing/payment
  /sistadm/billing/payment/{contract_id}/{year}/{month}
  /sistadm/billing/payment/reconcile
    /sistadm/billing/payment/reconcile/history
    /sistadm/billing/payment/reconcile/{contract_id}/{year}/{month}
    /sistadm/billing/payment/reconcile
  /sistadm/billing/late
    /sistadm/billing/late/all
    /sistadm/billing/late/{contract_id}
    /sistadm/billing/late/{contract_id}/calcfinanctimecorrection
  ------------
*/
Route::prefix('/sistadm')->group( function() {

  // -----------------------
  // === At /sistadm/billings
  // -----------------------
  Route::prefix('/billings')->group( function() {

    //===>>> sistadm/billings/ouverts
    Route::get('/ouverts', [
      'as'   => 'cobrancas.abertas',
      'uses' => 'Billing\CobrancaController@abertas'
    ]);
    //===>>> sistadm/billings/onref/{year?}/{month?}
    Route::get('/onref/{year?}/{month?}', [
      'as'   => 'cobrancas.onref',
      'uses' => 'Billing\CobrancaController@onref'
    ]);
    //===>>> sistadm/billings/onref/{year?}/{month?}
    Route::get('/onlyrent/onref/{year?}/{month?}', [
      'as'   => 'cobrancas.onlyrent.onref',
      'uses' => 'Billing\CobrancaController@onlyrent_onref'
    ]);
    //===>>> sistadm/billings/reconciled
    Route::get('reconciled', [
      'as'   => 'cobrancas.conciliadas',
      'uses' => 'Billing\CobrancaController@conciliadas'
    ]);
  }); // ends Route::prefix('/billings') //===>>> sistadm/billings

  // ----------------------
  // === At /sistadm/billing
  // ----------------------
  Route::prefix('/billing')->group( function() {

    //===>>> sistadm/billing/show/{cobranca_id}
    Route::get('show/{cobranca_id}', [
      'as'   => 'cobranca.mostrar',
      'uses' => 'Billing\CobrancaController@show'
    ]);

    //===>>> sistadm/billing/showviaref/{contract_id}/{year}/{month}
    Route::get('showviaref/{contract_id}/{year}/{month}', [
      'as'   => 'cobranca.mostrarviaref',
      'uses' => 'Billing\CobrancaController@showviaref'
    ]);

    //===>>> sistadm/billing/edit/{contract_id}/{year}/{month} GET
    Route::get('edit/{contract_id}/{year}/{month}', [
      'as'=>'cobranca.mensal.editar',
      'uses'=>'Billing\CobrancaController@edit_via_httpget'
    ]);
    //===>>> sistadm/billing/edit POST
    Route::post('/edit', [
      'as'=>'cobranca.mensal.editar',
      'uses'=>'Billing\CobrancaController@edit_via_httppost'
    ]);

    //===>>> sistadm/billing/analyzeverify/{contract_id}/{year}/{month} GET
    Route::get('analyzeverify/{cobranca_id}', [
      'as'=>'cobranca.mensal.analisarverificar',
      'uses'=>'Billing\CobrancaController@analyzeverify'
    ]);


    //===>>> sistadm/billing/createdynamic/{contract_id}/{year}/{month} GET
    Route::get('createdynamic/{contract_id}/{year}/{month}', [
      'as'=>'cobranca.mensal.criardinamicamente',
      'uses'=>'Billing\CobrancaController@createdynamic'
    ]);
    //===>>> sistadm/billing/createdynamic POST
    Route::post('/createdynamic', [
      'as'=>'cobranca.mensal.criardinamicamente',
      'uses'=>'Billing\CobrancaController@createdynamic'
    ]);


    // -------------------------------
    // === At /sistadm/billing/payments
    // -------------------------------
    Route::prefix('/payments')->group( function() {
      //===>>> sistadm/billing/payments
      Route::get('/', [
        'as'   => 'pagtos.contrato.historico',
        'uses' => 'Billing\PaymentController@history',
      ]);
      //===>>> sistadm/billing/payments/{contract_id}
      Route::get('/payments/{contract_id}', [
        'as'   => 'pagtos.contrato.historico',
        'uses' => 'Billing\PaymentController@list_per_contract',
      ]);
      //===>>> sistadm/billing/payments/ouverts
      Route::get('/ouverts', [
        'as'   => 'pagtos.abertos',
        'uses' => 'Billing\PaymentController@abertos',
      ]);
    }); // ends Route::prefix('/payments') //===>>> sistadm/billing/payments

    // ------------------------------
    // === At /sistadm/billing/payment
    // ------------------------------
    Route::prefix('/payment')->group( function() {

      //===>>> sistadm/billing/payment/{contract_id}/{year}/{month}
      Route::get('/{contract_id}/{year}/{month}', [
        'as'   => 'pagto',
        'uses' => 'Billing\PaymentController@listar_aberto'
      ]);

      // ----------------------------------------
      // === At /sistadm/billing/payment/reconcile
      // ----------------------------------------
      Route::prefix('/reconcile')->group( function() {

        //===>>> sistadm/billing/payment/reconcile/history
        Route::get('/history', [
          'as'   => 'conciliacao.historico',
          'uses' => 'Billing\PaymentController@reconcilehistory'
        ]);
        //===>>> sistadm/billing/payment/reconcile/{contract_id}/{year}/{month} GET
        Route::get('/{contract_id}/{year}/{month}', [
          'as'   => 'pagto.conciliar',
          'uses' => 'Billing\PaymentController@reconcile'
        ]);
        //===>>> sistadm/billing/payment/reconcile/{contract_id}/{year}/{month} POST
        Route::post('/reconcile', [     //{contract_id}/{year}/{month}
          'as'   => 'pagto.conciliar',
          'uses' => 'Billing\PaymentController@reconcile'
        ]);
      }); // ends Route::prefix('/reconcile') //===>>> sistadm/billing/payment/reconcile
    }); // ends Route::prefix('/payment') //===>>> sistadm/billing/payment

    // ---------------------------
    // === At /sistadm/billing/late
    // ---------------------------
    Route::prefix('/late')->group( function() {

      //===>>> /sistadm/billing/late/all
      Route::get('/all', [
        'as'   => 'cobrancas.emmora',
        'uses' => 'Billing\MoraDebitoController@listall'
      ]);
      //===>>> /sistadm/billing/late/{$contract_id}
      Route::get('/{contract_id}', [
      	'as'   => 'cobrancas.emmora.contrato',
        'uses' => 'Billing\MoraDebitoController@list'
      ]);
      //===>>> /sistadm/billing/late/{contract_id}/calcfinanctimecorrection
      Route::get('/{contract_id}/calcfinanctimecorrection', [
      	'as'   => 'cobrancas.emmora.exibirmoradebito',
        'uses' => 'Billing\MoraDebitoController@showcalcfinanctimecorrection'
      ]);
    }); // ends Route::prefix('/late')     //===>>> /sistadm/billing/late
  }); // ends Route::prefix('/billing') //===>>> /sistadm/billing
}); // ends Route::prefix('/sistadm') //===>>> /sistadm
