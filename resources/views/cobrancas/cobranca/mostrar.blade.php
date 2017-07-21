@extends('layouts.master')
@section('title')
    Exibir Cobrança
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')

  @if (empty($cobrancas))
    <h3>Não foram encontrados itens de cobrança.</h3>
    <h3>Por favor, se isto estiver incorreto, entrar em contato.</h3>
  @endif

  <?php use Carbon\Carbon; ?>




    @foreach ($cobrancas as $cobranca)
      <h2>Cobrança</h2>
      <h1>Aluguel e Encargos</h1>
      <br>
      <h3>Mês Ref.: {{ $cobranca->monthyeardateref->format('m/Y') }} </h3>
      <h3>Data de Vencimento: {{ $cobranca->duedate->format('d/m/Y') }}</h3>
      <h3>Hoje: {{ Carbon::today()->format('d/m/Y') }}</h3>
      <br>
      <br>
      @if ($cobranca->billingitems()->count()==0)
        <h3>Não foram encontrados itens de cobrança.</h3>
        <h3>Por favor, se isto estiver incorreto, entrar em contato.</h3>
      @endif

      @if ($cobranca->billingitems()->count() > 0)
        <h3>Itens:</h3>
        <table class="rwd-table">
          <tr>
            <th>Item</th>
            <th>Ref.</th>
            <th>Valor</th>
            <th> R </th>
          </tr>

        @foreach ($cobranca->billingitems()->get() as $billingitem)
          <tr>
            <td data-th="item">  {{ $billingitem->brief_description }} </td>
            <td data-th="ref">   {{ $billingitem->monthyeardateref->format('m-Y') }} </td>
            <td data-th="valor"> {{ $billingitem->charged_value }} </td>
            <td data-th="repasse"> * </td>
          </tr>
        @endforeach

        </table>
        <h3>Total: {{ $cobranca->total }}</h3>
        @endif  {{-- @if ( $cobranca->billingitems()->count() > 0 ) --}}

        <br>
        <?php
          $bankaccount = null;
          if ($cobranca->contract != null) {
            if ($bankaccount = $cobranca->contract->bankaccount != null) {
              $bankaccount = $cobranca->contract->bankaccount;
            }
          }
        ?>
        @if ($bankaccount != null)
          <h3>Dados para Depósito ou Transferência:</h3>
          <h4>Banco: {{ $bankaccount->bankname }} </h4>
          <h4>Agência: {{ $bankaccount->agency }} </h4>
          <h4>Conta-corrente: {{ $bankaccount->account }} </h4>
          <h4>A: {{ $bankaccount->customer }} </h4>
          <h4>CPF: {{ $bankaccount->cpf }} </h4>
        @endif  {{-- @if ($bankaccount != null) --}}

      @if ($cobranca->contract->imovel != null)
        <h5><a href="/condominios/{{ $cobranca->contract->imovel->id }}">
          Histórico das Tarifas de Condomínios.</a></h5>
      @endif

      @endforeach {{-- @foreach ($cobrancas as $cobranca) --}}



@endsection
