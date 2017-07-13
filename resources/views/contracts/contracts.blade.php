@extends('layouts.master')
@section('title')
    Exibir Contratos
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')
  <h1>Exibir Contratos</h1>

  @if (empty($contracts))
    <h2>Não há contratos para listar. (Se houver um erro aqui, por favor, entre em contato.)</h2>
  @endif

  @if (count($contracts)>0)
  <table class="rwd-table">
    <tr>
      <th>Contr.ID</th>
      <th>Objeto</th>
      <th>Valor Mensal</th>
      <th>Data Início</th>
      <th>Inquilino(a/s)</th>
    </tr>
    @foreach ($contracts as $contract)
      <?php
        $n_users = 0;
        if ($contract->users != null) {
          $n_users = $contract->users->count();
        }
        $endereco = "n/a";
        $imovel_href = "#";
        $current_rent_value = "";
        $imovel = $contract->imovel;
        // $tipo_imov = "";
        // $area_edif_iptu_m2 = "";
        if ($imovel != null) {
          $endereco = $imovel->get_street_address();
          $imovel_href = route('imovel.show', $imovel);
          // $tipo_imov = $imovel->tipo_imov;
          // $area_edif_iptu_m2	= $imovel->area_edif_iptu_m2;
        }
        ?>
        <tr>
          <td data-th="contract_id"> {{ $contract->id }} </td>
          <td data-th="imovel_endereco">  <a href="{{ $imovel_href }}"> {{ $endereco }} </a> </td>
          <td data-th="valor_aluguel"> {{ $contract->current_rent_value }} </td>
          <td data-th="data_inicio"> {{ $contract->start_date }}* </td>
          @if ($n_users==0)
            <td></td>
          @endif
          @if ($n_users > 0)
          <td colspan=" {{ $n_users }}">
            <table class="rwd-table">
              @foreach ($contract->users as $user)
                <tr>
                  <td data-th="inquilino">{{ $user->get_first_n_last_names() }}</td>
                  <td data-th="email"> {{ $user->email }} </td>
                </tr>
              @endforeach
            </table>
          </td>
          @endif  {{-- @if ($n_users > 0) --}}
        </tr>
    @endforeach
  </table>
  @endif  {{-- @if (count($contracts)>0) --}}
@endsection
