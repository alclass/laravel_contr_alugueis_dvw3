@extends('layouts.master')
@section('title')
    Controle Locação
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')


<h1>Locação dos Imóveis</h1>

<?php
  $total_valor_aluguel = 0;
?>

<table class="rwd-table">
  <tr>
    <th>Imóvel</th>
    <th>Endereço</th>
    <th>Valor</th>
    <th>Em dia?</th>
    <th>Inquilino(a) / Email</th>
  </tr>

@if(count($imoveis)>0)

  @foreach ($imoveis as $imovel)
    <?php
      $total_valor_aluguel = $total_valor_aluguel + $imovel->valor_aluguel;
      if ( $imovel->users->isEmpty() ) {
        // create dummy user row
        $user = new \App\User;
        $user->first_name = "Sem";
        $user->last_name = "Ocupação";
        $user->email = "---";
        $imovel->users->add($user);
      }
      $n_users = $imovel->users->count();
    ?>
      <tr>
        <td data-th="imovel_apelido"> {{ $imovel->apelido }} </td>
        <td data-th="imovel_endereco"> {{ $imovel->get_street_address() }} </td>

        <td data-th="valor_aluguel"> {{ $imovel->valor_aluguel }} </td>
        <td data-th="is_pay_on_date"> * </td>

        <td colspan=" {{ $n_users }}">
          <table class="rwd-table">
            @foreach ($imovel->users as $user)
              <tr>
                <td data-th="inquilino">{{ $user->name_first_last() }}</td>
                <td data-th="email"> {{ $user->email }} </td>
              </tr>
            @endforeach
          </table>
        </td>
      </tr>
  @endforeach

  <tr>
    <td> </td>
    <td> Total Aluguéis: </td>
    <td data-th="total_valor_aluguel"> {{ $total_valor_aluguel }} </td>
    <td> </td>
    <td> </td>
    <td > </td>
  </tr>

@endif

</table>

@endsection
