@extends('layouts.master')
@section('title')
    Register Pagamento Efetuado
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/zebra_datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/zebra_datepicker_examples.css') }}">
@endsection
@section('content')

<h1>Payment Registration</h1>

@if(!empty($payment))

  <h3>Payment realized:</h3>
  <h4>Payee:  {{ $payment->user->name_first_last() }}</h4>
  <h4>Imóvel: {{ $payment->imovel->get_street_address() }}</h4>
  <h4>Amount: {{ $payment->amount }}</h4>
  <h4>Deposited on: {{ $payment->deposited_on }}</h4>
  <h4>Bank: {{ $payment->bankname }}</h4>

@endif

<h1>New Payment Registration</h1>
<form name='registerpaymentform' method="POST" action="/registerpayment">
  <input type="hidden" name="_token" value="{{ csrf_token() }}">
  Amount:    <input name="amount"       type="text" value=""> <br>
  Date:      <div class="form-group"><input id="datepicker_deposited_on" name="deposited_on"  type="text"><br></div>
  Bank:      <input name="bankname" type="text" value=""> <br>
  User ID:   <input name="payer_id"      type="text"> <br>
  Imóvel ID: <input name="imovel_id"    type="textfield"> <br>
  <br>
  <input name="registerpaymentbutton" type="submit" value="Enviar"> </submit>
</form>
@endsection
@section('scripts')
  <script src="{{ URL::to('js/zebra_datepicker.min.js') }}"></script>
  <!--script src="{{ URL::to('js/datepicker_run.js') }}"></script-->
  <script>
    $(document).ready(function() {
      $('#datepicker_deposited_on').Zebra_DatePicker({
          format: 'd/m/Y'
        });
    });
  </script>
@endsection
