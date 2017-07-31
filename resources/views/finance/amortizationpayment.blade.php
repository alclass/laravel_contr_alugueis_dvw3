@extends('layouts.master')
@section('title')
    Exibir Cobranças
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')
<?php
  // Open up object $time_evolve_loan_obj available to this template
  // ===============================================================
  $column_keys    = $time_evolve_loan_obj->column_keys;
  $loan_ini_date  = $time_evolve_loan_obj->loan_ini_date;
  $loan_ini_value = $time_evolve_loan_obj->loan_ini_value;
  $loan_duration_in_months = $time_evolve_loan_obj->loan_duration_in_months;
  $rows = $time_evolve_loan_obj->rows;
  $pmt_prestacao_mensal_aprox_until_payment_end = $time_evolve_loan_obj->pmt_prestacao_mensal_aprox_until_payment_end;
  $n_remaining_months_on_pmt = $time_evolve_loan_obj->n_remaining_months_on_pmt;
  $interest_rate_pmt_aprox = $time_evolve_loan_obj->interest_rate_pmt_aprox;
  $msg_or_info = $time_evolve_loan_obj->msg_or_info;
?>
<h1>{{ $msg_or_info }}</h1>

  <table class="rwd-table">
    <tr>
      <th>Data Saldo</th>
      <th>Valor Início</th>
      <th>CM mês</th>
      <th>CM ap.</th>
      <th>CM+J ap.</th>
      <th>Corrigido</th>
      <th>Abatido</th>
      <th>Saldo</th>
    </tr>

  @foreach ($rows as $row)
    <?php
      // Extracting row fields
      $balance_date           = $row['balance_date'];
      $montante               = $row['montante'];
      $corrmonet_perc         = $row['corrmonet_perc'];
      $corrmonet_aplic_dias_perc  = $row['corrmonet_aplic_dias_perc'];
      $cm_n_juros_aplic_dias_perc = $row['cm_n_juros_aplic_dias_perc'];
      $montante_corrigido     = $row['montante_corrigido'];
      $abatido                = $row['abatido'];
      if ($abatido==0) {
        $abatido = '---';
      } else {
        $abatido = number_format($abatido, 2);
      }
      $saldo                  = $row['saldo'];
    ?>
    <tr>
      <td data-th="balance_date"> {{ $balance_date->format('d/m/Y') }} </td>
      <td data-th="montante">   {{ number_format($montante,2) }} </td>
      <td data-th="corrmonet_perc"> {{ number_format($corrmonet_perc, 3) }}% </td>
      <td data-th="corrmonet_aplic_dias_perc"> {{ number_format($corrmonet_aplic_dias_perc, 2) }}% </td>
      <td data-th="cm_n_juros_aplic_dias_perc"> {{ number_format($cm_n_juros_aplic_dias_perc, 3) }}% </td>
      <td data-th="montante_corrigido">   {{ number_format($montante_corrigido) }} </td>
      <td data-th="abatido"> {{ $abatido }} </td>
      <td data-th="saldo">   {{ number_format($saldo) }} </td>
    </tr>
  @endforeach
  </table>
  <p>Total dos itens: {{ count($rows) }} </p>
@endsection
