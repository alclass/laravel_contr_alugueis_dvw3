@extends('layouts.master')
@section('title')
    Listar Moras
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')

  @if (empty($moradebitos))
    <h3>Não foram encontradas cobranças com mora.</h3>
    <h3>Por favor, se isto estiver incorreto, entrar em contato.</h3>
  @endif

  <?php
    use Carbon\Carbon;
  ?>
  <h1>Listagem de Moras</h1>
  <h6>Em {{ Carbon::today()->format('d/m/Y') }}</h6>

  @foreach ($moradebitos as $moradebito)
    <?php
      $formatstr_monthrefdate = 'n/a';
      if ($moradebito->monthrefdate !=null) {
        $formatstr_monthrefdate = $moradebito->monthrefdate->format('M/Y');
      }
      $formatstr_ini_debt_date = 'n/a';
      if ($moradebito->ini_debt_date !=null) {
        $formatstr_ini_debt_date = $moradebito->ini_debt_date->format('M/Y');
      }
      $formatstr_changed_debt_date = 'n/a';
      if ($moradebito->changed_debt_date !=null) {
        $formatstr_changed_debt_date = $moradebito->changed_debt_date->format('M/Y');
      }
    ?>
    <br>
    <h3>Mora Ref.: {{ $formatstr_monthrefdate }} </h3>
    <h4>Valor original: {{ $moradebito->ini_debt_value }} </h4>
    <h4>Data: {{ $formatstr_ini_debt_date }} </h4>
    <h4>changed_debt_value: {{ $moradebito->changed_debt_value }} </h4>
    <h4>changed_debt_date: {{ $formatstr_changed_debt_date }} </h4>
    <h5>Ainda em aberto: {{ $moradebito->is_open }}</h5>
    <h5>lineinfo: {{ $moradebito->lineinfo }} </h5>
    <!-- h3>Info: {{-- $moradebito->lineinfo --}}</h3-->
    <br>
    {{-- var_dump($moradebito->copy_monthly_mora_fraction_index_array()) --}}
    <br>

    @foreach ($moradebito->get_explanation_lines() as $line)
      <h5>{{ $line }}</h5>
    @endforeach


  @endforeach  {{-- @foreach ($moradebitos as $moradebito) --}}

@endsection
