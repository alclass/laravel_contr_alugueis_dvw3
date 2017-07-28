@extends('layouts.master')
@section('title')
    Exibir Cobranças
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')
<?php
  $total_cobrancas = 0;
  $current_month = $loan_ini_date->copy();
?>
<h1>Pagamentos {{ $msg_or_info }}</h1>

{{-- $column_keys = ['value_ini_month', 'corrmonet_in_perc', 'corrected_value', 'abatido', 'saldo' ]; --}}

  <table class="rwd-table">
    <tr>
      <th>Mês</th>
      <th>Valor Início</th>
      <th>Corr. Monet. %</th>
      <th>Juros Fixos</th>
      <th>Corrigido</th>
      <th>Abatido</th>
      <th>Saldo</th>
    </tr>

  @foreach ($rows as $row)
    <?php
      $current_month = $current_month->addMonths(1);
      $value_ini_month   = $row['value_ini_month'];
      $corrmonet_in_perc = $row['corrmonet_in_perc'];
      $juros_fixos_in_perc = 10;
      $corrected_value   = $row['corrected_value'];
      $abatido  = $row['abatido'];
      $saldo    = $row['saldo'];
     ?>
    <tr>
      <td data-th="current_month"> {{ $current_month->format('M-Y') }} </td>
      <td data-th="value_ini_month">   {{ $value_ini_month }} </td>
      <td data-th="corrmonet_in_perc"> {{ $corrmonet_in_perc }}% </td>
      <td data-th="juros_fixos_in_perc"> {{ $juros_fixos_in_perc}}% </td>
      <td data-th="corrected_value">   {{ $corrected_value }} </td>
      <td data-th="abatido"> {{ $abatido }} </td>
      <td data-th="saldo">   {{ $saldo }} </td>
    </tr>
  @endforeach
  </table>
  <p>Total dos itens: {{ count($rows) }} </p>
@endsection
