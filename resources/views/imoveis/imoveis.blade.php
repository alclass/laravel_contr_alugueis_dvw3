@extends('layouts.master')
@section('title')
    Exibir Imóveis
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')
  <h1>Exibir Imóveis</h1>
  <?php
    $total_valor_alugueis = 0;
  ?>
  @if (empty($imoveis))
  <h2>Não há imóveis para listar. (Se houver um erro aqui, por favor, entre em contato.)</h2>
  @endif
  @if (count($imoveis)>0)
  <table class="rwd-table">
    <tr>
      <th>Imóvel</th>
      <th>Endereço</th>
      <th>Valor</th>
      <th>Em dia?</th>
      <th>Inquilino(a) / Email</th>
    </tr>
    @foreach ($imoveis as $imovel)
      {{-- @include('imovelpiecetemplate') --}}
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
        <tr>
          <td data-th="imovel_apelido"> {{ $imovel->apelido }} </td>
          <td data-th="imovel_endereco"> {{ $imovel->get_street_address() }} </td>

          <td data-th="valor_aluguel"> {{ $contract->current_rent_value }} </td>
          <td data-th="is_pay_on_date"> * </td>

          <td colspan=" {{ $n_users }}">
            <table class="rwd-table">
              @foreach ($contract->users as $user)
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
      <td data-th="total_valor_aluguel"> {{ $total_valor_alugueis }} </td>
      <td> </td>
      <td> </td>
      <td > </td>
    </tr>
  </table>
  @endif
  {{-- @if (count($imoveis)>0) --}}
@endsection
