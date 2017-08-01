@extends('layouts.master')
@section('title')
    Exibir Cobranças
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')
<?php
  // Open up object $borrower
  // ===============================================================
  $loan_ini_value = $borrower->loan_ini_value;
  $formatstr_loan_ini_date  = 'n/a';
  if ($borrower->loan_ini_date != null) {
    $formatstr_loan_ini_date  = $borrower->loan_ini_date->format('d/M/Y');
  }
  $loan_duration_in_months = $borrower->loan_duration_in_months;
  $amortization_parcels_evolver = $borrower->get_amortization_parcels_evolver();
  $column_keys = $amortization_parcels_evolver->column_keys;
  $rows = $amortization_parcels_evolver->rows;
  $pmt_prestacao_mensal_aprox_until_payment_end = $amortization_parcels_evolver->pmt_prestacao_mensal_aprox_until_payment_end;
  $n_remaining_months_on_pmt = $amortization_parcels_evolver->n_remaining_months_on_pmt;
  $interest_rate_pmt_aprox = $amortization_parcels_evolver->interest_rate_pmt_aprox;
  $msg_or_info = $amortization_parcels_evolver->msg_or_info;
?>
<p></p>

<h1>Tabela {{ $msg_or_info }}</h1>

<h5>Financiado(a): {{ $borrower->get_first_n_last_names() }} </h5>
<h5>Valor: {{ $loan_ini_value }} </h5>
<h6>Data: {{ $formatstr_loan_ini_date }} </h6>
<h6>Duração:{{ $loan_duration_in_months }}</h6>

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

  <?php
    // Extracting row fields
    $pmt_pretacao_aprox = number_format($amortization_parcels_evolver->pmt_prestacao_mensal_aprox_until_payment_end, 2);
    $interest_rate_pmt_aprox_perc = $amortization_parcels_evolver->interest_rate_pmt_aprox * 100;
    $interest_rate_pmt_aprox_perc = number_format($interest_rate_pmt_aprox_perc, 2);
  ?>

  <h5>Projeção</h5>
  <h5>PMT Valor Corrente (hoje) Projetado da Prestação: R$ {{ $pmt_pretacao_aprox }} </h5>
  <h6>Nº de meses restantes: {{ $n_remaining_months_on_pmt }}</h6>
  <h6>Na base projetada de CM+Juros de: {{ $interest_rate_pmt_aprox_perc }}% </h6>
  <p></p>
  <p></p>
  <p>copyright 2017</p>

@endsection
