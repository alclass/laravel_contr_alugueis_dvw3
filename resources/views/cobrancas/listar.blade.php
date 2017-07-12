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
      <th>Data Ref. Princ.</th>
      <th>Objeto</th>
      <th>Data Prazo</th>
      <th>Valor</th>
      <th>Em dia?</th>
    </tr>

  @foreach ($cobrancas as $cobranca)
    <tr>
      <td data-th="monthref">  {{ $cobranca->mainmonthref }} </td>
      <td data-th="objeto"> Contrato {{ $cobranca->mainmonthref }} </td>
      <td data-th="dataprazo"> {{ $cobranca->duedate }} </td>
      <td data-th="valor">     {{ $cobranca->get_valor() }} </td>
      <td data-th="is_pay_on_date"> * </td>
    </tr>
  @endforeach
  </table>
@endsection
