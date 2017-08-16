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
    @include('contracts.contract_pagepiece')
  @endforeach  {{-- @foreach ($user->contracts as $contract) --}}

@endsection
