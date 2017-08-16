@extends('layouts.master')
@section('title')
    Exibir Contrato
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')

<?php
  use Carbon\Carbon;
  $imovel = $contract->imovel;
  $street_address = 'n/a';
  if ($imovel != null) {
    $street_address = $imovel->get_street_address();
  }
  $today = Carbon::today();
  $year  = $today->year;
  $month = $today->month;
?>

  <h1>Exibir Contrato</h1>
  <h4> {{ $street_address }} </h4>
  <h5> {{ $contract->current_rent_value }} </h5>
  <h5>
    <a href="{{ route('cobranca.mostrar', [$contract->id, $year, $month]) }}">
          Ver cobrança atual
    </a>
  </h5>

  <h2>Contratante(s)</h2>
  @foreach($contract->users as $user)
    <h4> <a href="{{ route('persons.user', $user) }}">{{ $user->get_first_n_last_names() }} </a></h4>
    <h5> {{ $user->email }} </h5>
  @endforeach

@endsection
