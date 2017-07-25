@extends('layouts.master')
@section('title')
    Exibir Cobranças
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')

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
      $imovel_apelido = 'n/a';
      if ($cobranca->contract->imovel != null) {
        $imovel_apelido = $cobranca->contract->imovel->apelido;
      }
      // parameters for route('cobranca.mostrar'...)
      $contract_id = $cobranca->contract->id;
      $year_ref    = $cobranca->monthyeardateref->year;
      $month_ref   = $cobranca->monthyeardateref->month;
     ?>
    <tr>
      <td data-th="imovel"> {{ $imovel_apelido }} </td>
      <td data-th="monthref">  {{ $cobranca->monthyeardateref->format('M/Y') }} </td>
      <td data-th="prazopagto"> {{ $cobranca->duedate->format('d/M/Y') }} </td>
      <td data-th="valor">     {{ $cobranca->total_value }} </td>
      {{-- route('cobranca.mostrar', --}}
      <td data-th="is_pay_on_date"> <a href="{{ route('cobranca.mostrar',
        [$contract_id, $year_ref, $month_ref])}}">
         visualizar </a></td>
    </tr>
  @endforeach
  </table>
@endsection
