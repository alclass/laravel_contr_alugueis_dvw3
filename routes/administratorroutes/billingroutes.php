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
  /sa/billing
  ---------------
  /sa/billings
    /sa/billings/ouverts
    /sa/billings/reconciled
  /sa/billing
    /sa/billing/show/{cobranca_id}
    /sa/billing/showbyref/{contract_id}/{year}/{month}
    /sa/billing/edit/{contract_id}/{year}/{month}
    /sa/billing/edit

    /sa/billing/verify/{contract_id}/{year}/{month}
    /sa/billing/createdynamic/{contract_id}/{year}/{month}

  /sa/billing/items
    /sa/billing/items/maintain/{contract_id}/{year}/{month}


  /sa/billing/payments
    /sa/billing/payments/{contract_id}
    /sa/billing/payments/ouverts
  /sa/billing/payment
  /sa/billing/payment/{contract_id}/{year}/{month}
  /sa/billing/payment/reconcile
    /sa/billing/payment/reconcile/history
    /sa/billing/payment/reconcile/{contract_id}/{year}/{month}
    /sa/billing/payment/reconcile
  /sa/billing/late
    /sa/billing/late/all
    /sa/billing/late/{contract_id}
    /sa/billing/late/{contract_id}/calcfinanctimecorrection
  ------------
*/

Route::prefix('/submit')->group( function() {
  Route::get('/billingitems', [
    'as'   => 'billingitemscreatorroute',
    'uses' => function() {
      return view('cobrancas.cobranca.billingitemscreator');
    }
  ]);
});

Route::prefix('/sa')->group( function() {

  // -----------------------
  // === At /sa/billings
  // -----------------------
  Route::prefix('/billings')->group( function() {

    //===>>> sa/billings/ouverts
    Route::get('/ouverts', [
      'as'   => 'cobrancas.abertas',
      'uses' => 'Billing\CobrancaController@abertas'
    ]);
    //===>>> sa/billings/onref/{year?}/{month?}
    Route::get('/onref/{year?}/{month?}', [
      'as'   => 'cobrancas.onref',
      'uses' => 'Billing\CobrancaController@onref'
    ]);
    //===>>> sa/billings/onref/{year?}/{month?}
    Route::get('/onlyrent/onref/{year?}/{month?}', [
      'as'   => 'cobrancas.onlyrent.onref',
      'uses' => 'Billing\CobrancaController@onlyrent_onref'
    ]);
    //===>>> sa/billings/reconciled
    Route::get('reconciled', [
      'as'   => 'cobrancas.conciliadas',
      'uses' => 'Billing\CobrancaController@conciliadas'
    ]);
    Route::get('/historico/{imovelapelido}', [
      'as'   => 'cobrancasporimovelroute',
      'uses' => 'Billing\CobrancaController@listar_cobrancas_por_imovel'
    ]);


  }); // ends Route::prefix('/billings') //===>>> sa/billings

  // ----------------------
  // === At /sa/billing
  // ----------------------
  Route::prefix('/billing')->group( function() {

    //===>>> sa/billing/show/{cobranca_id}
    Route::get('show/{cobranca_id}', [
      'as'   => 'cobrancaviaidroute',
      'uses' => 'Billing\CobrancaController@show'
    ]);

    Route::get('ref/{year}/{month}/{imovelapelido}/{monthseqnumber?}', [
      'as'   => 'cobrancaviayearmonthimovapelroute',
      'uses' => 'Billing\CobrancaController@show_by_year_month_imovelapelido'
    ]);

    //===>>> sa/billing/showviaref/{contract_id}/{year}/{month}
    Route::get('showviaref/{contract_id}/{year}/{month}', [
      'as'   => 'cobranca.mostrarviaref',
      'uses' => 'Billing\CobrancaController@showviaref'
    ]);


    //===>>> sa/billing/edit/{contract_id}/{year}/{month} GET
    Route::get('edit/{year}/{month}/{imovelapelido}/{monthseqnumber?}', [
      'as'=>'cobrancaeditarhttpgetroute',
      'uses'=>'Billing\CobrancaController@edit_via_httpget',
      // 'uses'=> function($year, $month, $imovelapelido, $monthseqnumber) { return 'hi' . $year . '/' . $month . '/' . $imovelapelido . '/' . $monthseqnumber; }
    ]);

    Route::get('xedit/{x}', ['uses' => function($x) { return 'hi'.$x; }]);

    //===>>> sa/billing/edit POST
    Route::post('edit/', [
      'as'=>'cobrancaeditarhttppostroute',
      'uses'=>'Billing\CobrancaController@edit_via_httppost'
    ]);

    //===>>> sa/billing/analyzeverify/{contract_id}/{year}/{month} GET
    Route::get('analyzeverify/{cobranca_id}', [
      'as'=>'cobranca.mensal.analisarverificar',
      'uses'=>'Billing\CobrancaController@analyzeverify'
    ]);


    //===>>> sa/billing/createdynamic/{contract_id}/{year}/{month} GET
    Route::get('createdynamic/{contract_id}/{year}/{month}', [
      'as'=>'cobranca.mensal.criardinamicamente',
      'uses'=>'Billing\CobrancaController@createdynamic'
    ]);
    //===>>> sa/billing/createdynamic POST
    Route::post('/createdynamic', [
      'as'=>'cobranca.mensal.criardinamicamente',
      'uses'=>'Billing\CobrancaController@createdynamic'
    ]);


    // -------------------------------
    // === At /sa/billing/payments
    // -------------------------------
    Route::prefix('/payments')->group( function() {
      //===>>> sa/billing/payments
      Route::get('/', [
        'as'   => 'pagtos.contrato.historico',
        'uses' => 'Billing\PaymentController@history',
      ]);
      //===>>> sa/billing/payments/{contract_id}
      Route::get('/payments/{contract_id}', [
        'as'   => 'pagtos.contrato.historico',
        'uses' => 'Billing\PaymentController@list_per_contract',
      ]);
      //===>>> sa/billing/payments/ouverts
      Route::get('/ouverts', [
        'as'   => 'pagtos.abertos',
        'uses' => 'Billing\PaymentController@abertos',
      ]);
    }); // ends Route::prefix('/payments') //===>>> sa/billing/payments

    // ------------------------------
    // === At /sa/billing/payment
    // ------------------------------
    Route::prefix('/payment')->group( function() {

      //===>>> sa/billing/payment/{contract_id}/{year}/{month}
      Route::get('/{contract_id}/{year}/{month}', [
        'as'   => 'pagto',
        'uses' => 'Billing\PaymentController@listar_aberto'
      ]);

      // ----------------------------------------
      // === At /sa/billing/payment/reconcile
      // ----------------------------------------
      Route::prefix('/reconcile')->group( function() {

        //===>>> sa/billing/payment/reconcile/history
        Route::get('/history', [
          'as'   => 'conciliacao.historico',
          'uses' => 'Billing\PaymentController@reconcilehistory'
        ]);
        //===>>> sa/billing/payment/reconcile/{contract_id}/{year}/{month} GET
        Route::get('/{contract_id}/{year}/{month}', [
          'as'   => 'pagto.conciliar',
          'uses' => 'Billing\PaymentController@reconcile'
        ]);
        //===>>> sa/billing/payment/reconcile/{contract_id}/{year}/{month} POST
        Route::post('/reconcile', [     //{contract_id}/{year}/{month}
          'as'   => 'pagto.conciliar',
          'uses' => 'Billing\PaymentController@reconcile'
        ]);
      }); // ends Route::prefix('/reconcile') //===>>> sa/billing/payment/reconcile
    }); // ends Route::prefix('/payment') //===>>> sa/billing/payment

    // ---------------------------
    // === At /sa/billing/late
    // ---------------------------
    Route::prefix('/late')->group( function() {

      //===>>> /sa/billing/late/all
      Route::get('/all', [
        'as'   => 'cobrancas.emmora',
        'uses' => 'Billing\MoraDebitoController@listall'
      ]);
      //===>>> /sa/billing/late/{$contract_id}
      Route::get('/{contract_id}', [
      	'as'   => 'cobrancas.emmora.contrato',
        'uses' => 'Billing\MoraDebitoController@list'
      ]);
      //===>>> /sa/billing/late/{contract_id}/calcfinanctimecorrection
      Route::get('/{contract_id}/calcfinanctimecorrection', [
      	'as'   => 'cobrancas.emmora.exibirmoradebito',
        'uses' => 'Billing\MoraDebitoController@showcalcfinanctimecorrection'
      ]);
    }); // ends Route::prefix('/late')     //===>>> /sa/billing/late
  }); // ends Route::prefix('/billing') //===>>> /sa/billing
}); // ends Route::prefix('/sa') //===>>> /sa
