@extends('layouts.master')
@section('title')
    Controle Locação
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')

<h1>Payment Registration</h1>

<h1>New Payment Registration</h1>
  <form name='registerpayform' method="POST" action="#">
    
    Amount: <input name="amount" type="text" value=""> <br>
    Date: <input name="deposited_on" type="text" value=""> <br>
    User ID: <input name="user_id" type="text"> <br>
    Imóvel ID: <input name="imovel_id" type="textfield"> <br>
    <br>
    <input name="registerpay" type="submit" value="Enviar"> </submit>
  </form>

@endsection
