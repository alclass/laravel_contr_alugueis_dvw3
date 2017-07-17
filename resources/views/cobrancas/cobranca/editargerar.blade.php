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

    $valor_recebido    = $conciliar_aarray['valor_recebido'];
    $meio_de_pagto     = $conciliar_aarray['meio_de_pagto'];
    $data_recebido     = $conciliar_aarray['data_recebido'];
    $debito_ou_credito = $conciliar_aarray['debito_ou_credito'];

    $value_debito_ou_credito = (float) $debito_ou_credito;

    /* If there's $debito_ou_credito,
         it's necessary to create the new Cobranca or pick it up if exists
    */

    $next_cobranca = $cobranca->createOrFindNextMonthCobranca();
    $billingitem   = $next_cobranca->createOrFindBilligItemForDebitoCredito(
      $monthyeardateref_of_item,
      $value_debito_ou_credito
    );


  ?>
  <h4>Valor_recebido: {{ $valor_recebido }}</h4>
  <h4>meio_de_pagto: {{ $meio_de_pagto }}</h4>
  <h4>data_recebido: {{ $data_recebido }}</h4>
  <br>
  <h4>Item:</h4>
  <h5>Valor: {{ $billingitem->charged_value }}</h5>
  <h5>Descr.: {{ $billingitem->brief_description }}</h5>
  <h5>Ref.: {{ $billingitem->monthyeardateref }}</h5>
  <br>

  <h5>Next Cobrança</h5>
  <h5>Contr. ID {{ $next_cobranca->contract_id }}</h5>
  <h5>Ref.: {{ $next_cobranca->monthyeardateref }}</h5>
  <h5>Duedate {{ $next_cobranca->duedate }}</h5>


@endsection
