@extends('layouts.master')
@section('title')
    Controle Locação
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/zebra_datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/zebra_datepicker_examples.css') }}">
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

  <?php
    $cobranca->load('billingitems');
  ?>

  @foreach ($cobrancas as $cobranca)

    <tr>
      <td data-th="monthref"> {{ $cobranca->mainmonthref }} </td>
      <td data-th="objeto"> Contrato {{ $cobranca->contract_id }} </td>
      <td data-th="dataprazo"> {{ $cobranca->duedate }} </td>
      <td data-th="valor"> {{ $cobranca->get_valor() }} </td>
      <td data-th="is_pay_on_date"> * </td>
    </tr>

    @foreach ($cobranca->billingitems as $billingitem)
      <tr>
        <td data-th="monthref"> {{ $billingitem->monthref }} </td>
        <td data-th="objeto"> {{ $billingitem->billing_item_type->billing_type_brief_description }} </td>
        <td data-th="dataprazo"> {{ $cobranca->duedate }} </td>
        <td data-th="valor"> {{ $billingitem->value }} </td>
        <td data-th="is_pay_on_date"> * </td>
      </tr>
    @endforeach

 @endforeach

  </table>

@endsection
