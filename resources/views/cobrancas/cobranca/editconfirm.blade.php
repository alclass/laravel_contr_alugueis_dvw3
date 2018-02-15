@extends('layouts.master')
@section('title')
    Exibir Cobrança
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')
<?php
  $contract    = $cobranca->contract;
  $imovel      = $contract->imovel;
  $bankaccount = $cobranca->bankaccount;
  if (!isset($today)) {
    $today = \Carbon\Carbon::today();
  }
?>
<div class="container">
<div class="row">
  <div class="well col-xs-10 col-sm-10 col-md-6 col-xs-offset-1 col-sm-offset-1 col-md-offset-2">
    <div class="row">
      <div class="col-xs-6 col-sm-6 col-md-6">
        <address>
          @foreach ($contract->users as $user)
            <strong>{{ $user->get_first_n_last_names() }}</strong>
            <br>
          @endforeach
          <span style="font-size:12px">Contrato de Locação Residencial <br>
            {{ $imovel->get_street_address() }}</span>
          <br>
          <abbr title="cep"></abbr>
        </address>
      </div>
      <div class="col-xs-6 col-sm-6 col-md-6 text-right">
          <p>
            <p style="font-size:11px"> Rio de Janeiro,
              {{ $today->format('d M Y') }} </p>
            Ref.: <strong>{{ $cobranca->monthrefdate->format('M/Y') }}</strong>
          </p>
      </div>
  </div>  <!-- ends class row-->

@include('cobrancas.cobranca.mostrar_inner')


<p>

  <form id="form_id" class="form-horizontal"
    action="{{ route('savecobrancahttppostroute') }}" method="post">

    <div align="center">
      {{ csrf_field() }}
      <button type="submit" name="button">Confirmar Criação/Edição da Cobrança</button>
      <hr>
    </div>

  </form>
</p>

</div>
</div>
</div>
@endsection
