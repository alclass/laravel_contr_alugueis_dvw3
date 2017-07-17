@extends('layouts.master')
@section('title')
    Histórico das Tarifas de Condomínio
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')
  <h1>Histórico das Tarifas de Condomínio</h1>
  <h4>Prédio {{ $imovel->predio_nome }}</h4>
  <h5> <a href="{{ route('imovel.show', $imovel->id) }}">{{ $imovel->get_address_without_complement() }}</a></h5>
  @if (count($condominiotarifas)==0)
    <br>
    <h4>Não há registros dos valores das tarifas de condomínio no banco de dados.</h4>
  @endif

  @if (count($condominiotarifas)>0)
    <?php
      $n_seq = 0;
    ?>
    <table class="rwd-table">
      <tr>
        <th>n.</th>
        <th>Ref.</th>
        <th>Tarifa</th>
      </tr>
      @foreach($condominiotarifas as $condominiotarifa)
        <tr>
          <?php
            $n_seq += 1;
          ?>
          <td data-th="n"> {{ $n_seq }} </td>
          <td data-th="ref"> {{ $condominiotarifa->format_monthyeardateref_as_m_slash_y() }} </td>
          <td data-th="tarifa"> {{ $condominiotarifa->tarifa_valor }} </td>
        </tr>
      @endforeach
    </table>
    <?php
      $triple_stats = $condominiotarifa->media_min_max_das_tarifas($condominiotarifas);
      $media = $triple_stats['media'];
      $min   = $triple_stats['min'];
      $max   = $triple_stats['max'];
    ?>
    <h5> Média: {{ $media }} </h5>
    <h5> Mínimo: {{ $min }} </h5>
    <h5> Máximo: {{ $max }} </h5>
  @endif
<br>
<h6>Sistema de Locação</h6>
@endsection
