@extends('layouts.master')
@section('title')
    Exibir Cobrança
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')
  <?php
    use Carbon\Carbon;

    $valor_recebido  = $conciliar_aarray['valor_recebido'];
    $meio_de_pagto   = $conciliar_aarray['meio_de_pagto'];
    $data_recebido   = $conciliar_aarray['data_recebido'];
    $mora_ou_credito = $conciliar_aarray['mora_ou_credito'];

    $valor_negativo_mora_positivo_credito = (float) $mora_ou_credito;

    /* If there's $mora_ou_credito, ie, it's != 0
         it's necessary to create the new Cobranca or pick it up if exists
    */

    $monthrefdate_anterior = $cobranca->monthrefdate;
    $next_cobranca = $cobranca->createIfNeededBillingItemForMoraOrCreditoMonthlyRef(
      $valor_negativo_mora_positivo_credito,
      $monthrefdate
    );
    // $next_cobranca = $next_cobrancas->first();
    var_dump($next_cobranca);
    $billingitems = array();
    if ($value_mora_ou_credito != 0) {
      $billingitems = $next_cobranca->createOrRetrieveAnyBillingItemsForMoraOrCredito(
        $monthrefdate_anterior,
        $value_mora_ou_credito
      );
    }
  ?>
  <h4>Valor_recebido: {{ $valor_recebido }}</h4>
  <h4>meio_de_pagto: {{ $meio_de_pagto }}</h4>
  <h4>data_recebido: {{ $data_recebido }}</h4>
  <br>

  @foreach ($billingitems as $billingitem)

    <h4>Item:</h4>
    <h5>Valor: {{ $billingitem->charged_value }}</h5>
    <h5>Descr.: {{ $billingitem->brief_description }}</h5>
    <h5>Ref.: {{ $billingitem->monthrefdate }}</h5>
    <br>

    <h5>Next Cobrança</h5>
    <h5>Contr. ID {{ $next_cobranca->contract_id }}</h5>
    <h5>Ref.: {{ $next_cobranca->monthrefdate }}</h5>
    <h5>Duedate {{ $next_cobranca->duedate }}</h5>

  @endforeach  {{-- @foreach ($billingitems as $billingitems) --}}


@endsection
