
@extends('layouts.master')
@section('title')
    Controle Locação
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')


<h1>Locação dos Imóveis</h1>
<table class="rwd-table">
  <tr>
    <th>Inquilino</th>
    <th>Imóvel</th>
    <th>Endereço</th>
    <th>E-mail</th>
    <th>Em dia?</th>
  </tr>

@if(count($users)>0)

@foreach ($users as $user)

<?php
  $imovel_endereco = 'bla';
  $imovel_apelido = 'bla b';
  if ( ! $user->imoveis->isEmpty() ) {
    $imovel = $user->imoveis->first();
    $imovel_endereco = $imovel->logradouro;
    $imovel_apelido  = $imovel->apelido;
  }
?>

  <tr>
    <td data-th="inquilino"> {{ $user->name_first_last() }} </td>
    <td data-th="imovel_apelido"> {{ $imovel_apelido }} </td>
    <td data-th="imovel_endereco"> {{ $imovel_endereco }} </td>
    <td data-th="email"> {{ $user->email }} </td>
    <td data-th="is_pay_on_date"> * </td>
  </tr>
@endforeach

@endif

</table>

@endsection
