
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

  <?php
    $contract = $imovel->get_current_rent_contract_if_any();
    $n_users = 0;
    if ($contract != null) {
      if ( $contract->users->isEmpty() ) {
        // create a dummy user row, just for the table presentation, it won't be db-saved
        $user = new \App\User;
        $user->first_name = "Sem";
        $user->last_name = "Ocupação";
        $user->email = "---";
        $contract->users->add($user);
      }
    }
  ?>
  @if ($contract == null)
    <h5> Não há contratos no banco de dados relativos a este imóvel. </h5>
  @endif
  @if ($contract != null)
    <h5> <a href="{{ route('contract', $contract->id) }}">Contrato Atual</a> {{ $contract->start_date }} a {{ $contract->get_end_date() }}</h5>
    <h5> Nº contratante(s): {{ $contract->users->count() }} </h5>
    <h5> Aluguel Valor Atual: {{ $contract->current_rent_value }} | Próximo reajuste: {{ $contract->start_date }}</h5>
    <h2>Inquilino(s)</h2>
    @foreach($contract->users as $user)
      <h4> <a href="{{ route('user.route', $user) }}">{{ $user->get_first_n_last_names() }} </a></h4>
      <h5> {{ $user->email }} </h5>
    @endforeach
  @endif

@endsection
