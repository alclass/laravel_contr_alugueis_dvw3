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
<?php
  $contracts = $user->contracts;
?>
@if (empty($contracts))
  <h4> Atualmente não há um contrato vigente.</h4>
@endif

@if (!empty($contracts))
  @foreach($contracts as $contract)
    <h3> Dados Resumidos do Contrato de Aluguel</h3>
    @foreach($contract->imoveis as $imovel)
      <h3>Imóvel</h3>
      <h4> Endereço:  <a href="{{ route('imovel.show', $imovel) }}">{{ $imovel->get_street_address() }} </a></h4>
      <h4> Tipo: {{ $imovel->tipo_imov }} </h4>
      <h4> Área IPTU: {{ $imovel->m2_no_iptu }} m2</h4>
      <h4> Valor Aluguel: {{ $contract->current_rent_value }} </h4>
    @endforeach
  @endforeach
@endif
{{-- @if ()!empty($contracts)) --}}
@endsection
