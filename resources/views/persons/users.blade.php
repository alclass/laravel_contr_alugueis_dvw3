@extends('layouts.master')
@section('title')
    Sistema de Controle Locação
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')

<h1>Locação dos Imóveis</h1>
@if (empty($users))
  <h3>Não há usuários/inquilinos no banco de dados.</h3>
@endif {{-- @if (empty($users)) --}}

@if (count($users) > 0)
  <table class="rwd-table">
    <tr>
      <th>Inquilino</th>
      <th>Total Contr.</th>
      <th>Imóv.Contr. Vig.</th>
      <th>Endereço</th>
      <th>E-mail</th>
      <th>Em dia?</th>
    </tr>
  @foreach ($users as $user)
    <?php
      $n_contracts = $user->contracts->count();
      $contract = $user->contracts->first();
      $imovel_apelido  = 'não há';
      $imovel_endereco = 'não há';
      if ($contract != null) {
        $imovel_apelido  = $contract->imovel->apelido;
        $imovel_endereco = $contract->imovel->get_street_address();
      }
    ?>
    <tr>
      <td data-th="inquilino"> <a href="{{ route('persons.user', $user) }}">{{ $user->get_first_n_last_names() }} </a></td>
      <td data-th="total_contratos"> {{ $n_contracts }} </td>
      <td data-th="imovel_apelido"> {{ $imovel_apelido }} </td>
      <td data-th="imovel_endereco"> {{ $imovel_endereco }} </td>
      <td data-th="email"> {{ $user->email }} </td>
      <td data-th="is_pay_on_date"> * </td>
    </tr>
  @endforeach  {{-- @foreach ($users as $user) --}}
  </table>
@endif {{-- @if (count($users) > 0) --}}

@endsection
