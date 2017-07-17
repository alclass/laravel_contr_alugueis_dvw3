@extends('layouts.master')
@section('title')
    Sistema de Controle Locação
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')
  <h1>Inquilino</h1>
  <h4> {{ $user->get_first_n_last_names() }} </h4>
  <h5> {{ $user->email }} </h5>
  @if (empty($user->contracts))
    <h4> Atualmente não há um contrato vigente.</h4>
  @endif {{-- @if (empty($user->contracts)) --}}

  @foreach ($user->contracts as $contract)
    <h3> Dados Resumidos do  <a href="{{ route('contract', $contract) }}"> Contrato ID {{ $contract->id }}</a></h3>
    <?php
      $endereco = "n/a";
      $imovel_href = "#";
      $current_rent_value = "";
      $tipo_imov = "";
      $area_edif_iptu_m2 = "";
      $imovel = $contract->imovel;
      if ($imovel != null) {
        $endereco = $imovel->get_street_address();
        $imovel_href = route('imovel.show', $imovel);
        $tipo_imov = $imovel->tipo_imov;
        $area_edif_iptu_m2	= $imovel->area_edif_iptu_m2;
      }
    ?>
    <h3> Imóvel {{ $tipo_imov }} </h3>
    <h4> Endereço:  <a href="{{ $imovel_href }}"> {{ $endereco }} </a></h4>
    <h4> Área IPTU: {{ $area_edif_iptu_m2	 }} m2</h4>
    <h4> Início do Contrato: {{ $contract->start_date }} </h4>
    <h5> Aluguel no Início do Contrato:  {{ $contract->initial_rent_value }} </h5>
    <h4> Valor Atual do Aluguel:   {{ $contract->current_rent_value }} </h4>
    <h5> Próximo Reajuste: {{ $contract->find_rent_value_next_reajust_date() }} </h4>
      {{-- ->toFormattedDateString() --}}
  @endforeach  {{-- @foreach ($user->contracts as $contract) --}}

@endsection
