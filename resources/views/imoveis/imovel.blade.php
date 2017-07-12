
@extends('layouts.master')
@section('title')
    Exibir Imóvel
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')

  <h1>Exibir Imóvel</h1>
  <h4> {{ $imovel->get_street_address() }} </h4>
  <h5> {{ $imovel->valor_aluguel  }} </h5>

  <?php
    $contract = $imovel->get_current_rent_contract_if_any();
    if (!empty($contract) && $contract->users->isEmpty() ) {
      // create dummy user row
      $user = new \App\User;
      $user->first_name = "Sem";
      $user->last_name = "Ocupação";
      $user->email = "---";
      $contract->users->add($user);
    }
    $n_users = $contract->users->count();
    $total_valor_alugueis += $contract->current_rent_value;
  ?>

  @if (!empty($contract))
    <h2>Inquilino(s)</h2>
    @foreach($contract->users as $user)
      <h4> <a href="{{ route('user.route', $user) }}">{{ $user->name_first_last() }} </a></h4>
      <h5> {{ $user->email }} </h5>
    @endforeach
  @endif

@endsection
