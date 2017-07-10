
@extends('layouts.master')
@section('title')
    Controle Locação
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwdtable.css') }}">
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
    <tr>
      <td data-th="inquilino"> {{ $user['inquilino'] }} </td>
      <td data-th="imovel_apelido"> {{ $user['imovel_apelido'] }} </td>
      <td data-th="imovel_endereco"> {{ $user['imovel_endereco'] }} </td>
      <td data-th="email"> {{ $user['email'] }} </td>
      <td data-th="is_pay_on_date"> {{ $user['is_pay_on_date'] }} </td>
    </tr>
    <tr>
      <td data-th="inquilino"> {{ $user->inquilino }} </td>
      <td data-th="imovel_apelido"> {{ $user->imovel_apelido }} </td>
      <td data-th="imovel_endereco"> {{ $user->imovel_endereco }} </td>
      <td data-th="email"> {{ $user->email }} </td>
      <td data-th="is_pay_on_date"> {{ $user->is_pay_on_date }} </td>
    </tr>

  @endforeach

@endif

</table>

@endsection
