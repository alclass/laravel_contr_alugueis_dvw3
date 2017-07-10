@extends('layouts.master')
@section('title')
    Controle Locação
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')

<h1>Payment Registration</h1>

<?php
  $payments = null;
?>

@if(!empty($payment))

  <h3>Payment realized:</h3>
  <h4>Payee:  {{ $payment->user->name_first_last() }}</h4>
  <h4>Imóvel: {{ $payment->imovel->get_street_address() }}</h4>
  <h4>Amount: {{ $payment->amount }}</h4>
  <h4>Deposited on: {{ $payment->deposited_on }}</h4>

@endif

<h1>New Payment Registration</h1>
  <form name='registerpayform' method="POST" action="{{ route('registerpay_get.route') }}">
    {!! csrf_field() !!}
    Amount: <input name="amount" type="text" value=""> <br>
    Date: <input name="deposited_on" type="text" value=""> <br>
    User ID: <input name="user_id" type="text"> <br>
    Imóvel ID: <input name="imovel_id" type="textfield"> <br>
    <br>
    <input name="registerpay" type="submit" value="Enviar"> </submit>
  </form>
@endsection
