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
?>

<h1>Cobranças {{ $category_msg }}</h1>

  <table class="rwd-table">
    <tr>
      <th>Imóvel</th>
      <th>Ref.</th>
      <th>Prazo Pagto</th>
      <th>Valor</th>
      <th>Enlace</th>
    </tr>

  @foreach ($cobrancas as $cobranca)
    <?php
      $valor_cobranca   = $cobranca->get_total_value();
      $total_cobrancas += $cobranca->get_total_value();
      $imovel_apelido = 'n/a';
      if ($cobranca->contract->imovel != null) {
        $imovel_apelido = $cobranca->contract->imovel->apelido;
      }
      // parameters for route('cobranca.mostrar'...)
      $contract_id = $cobranca->contract->id;
      $year_ref    = $cobranca->monthrefdate->year;
      $month_ref   = $cobranca->monthrefdate->month;
      $cobranca_formatstrduedate = 'n/a';
      if ($cobranca->monthrefdate != null) {
        $cobranca_formatstrmonthrefdate = $cobranca->monthrefdate->format('d/M/Y');
      }
      if ($cobranca->duedate != null) {
        $cobranca_formatstrduedate = $cobranca->duedate->format('d/M/Y');
      }
     ?>
    <tr>
      <td data-th="imovel">     {{ $imovel_apelido }} </td>
      <td data-th="monthref">   {{ $cobranca_formatstrmonthrefdate }} </td>
      <td data-th="prazopagto"> {{ $cobranca_formatstrduedate }} </td>
      <td data-th="valor">      {{ $valor_cobranca }} </td>
      {{-- route('cobranca.mostrar', --}}
      <td data-th="is_pay_on_date"> <a href="{{ route('cobrancaviayearmonthimovapelroute', $cobranca->urlrouteparamsasarray) }}">
         visualizar </a></td>
    </tr>
  @endforeach
  </table>
  <p>Total dos itens: {{ $total_cobrancas }} </p>
@endsection
