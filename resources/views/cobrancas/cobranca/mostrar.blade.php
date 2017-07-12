@extends('layouts.master')
@section('title')
    Exibir Cobrança
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')
<?php
  use Carbon\Carbon;
  // use App\Contract;
  $today = Carbon::now();
  $cobranca = contract->cobrancas->where('has_been_paid', 0)->get();
?>
@if (empty($cobranca))
  <h3>Não foram encontrados itens de cobrança.</h3>
  <h3>Por favor, se isto estiver incorreto, entrar em contato.</h3>
@endif

@if (!empty($cobranca))
  <h2>Cobrança</h2>
  <h1>Aluguel e Encargos</h1>
  <br>
  <h3>Mês Ref.: {{ $cobranca->mainmonthref }} </h3>
  <h3>Data de Vencimento: {{ $cobranca->duedate }}</h3>
  <h3>Hoje: {{ $today }}</h3>
  <br>
  <br>
  <?php
    $total = 0;
    $billingitems = $cobranca->billingitems;
  ?>
  @if (empty($billingitems))
    <h3>Não foram encontrados itens de cobrança.</h3>
    <h3>Por favor, se isto estiver incorreto, entrar em contato.</h3>
  @endif

  @if (!empty($billingitems))
    <h3>Itens:</h3>
    <table class="rwd-table">
      <tr>
        <th>Item</th>
        <th>Ref.</th>
        <th>Valor</th>
        <th> R </th>
      </tr>
    @foreach ($billingitems as $billingitem)
      <?php
        $billing_item_type = $billingitem->billing_item_type;
        $valor_item = $billingitem->value;
        $total += $valor_item;
      ?>
      <tr>
        <td data-th="item">  {{ $billing_item_type->brief_description }} </td>
        <td data-th="ref">   {{ $billingitem->monthref }} </td>
        <td data-th="valor"> {{ $billingitem->value }} </td>
        <td data-th="item">  {{ $billing_item_type->repasse_ou_branco() }} </td>
      </tr>
    @endforeach
    </table>

    <h3>Total: {{ $cobranca->get_valor_total() }}</h3>
    <h3>Total: {{ $total }}</h3>
    <br>
    <?php
      $bankaccount = $cobranca->bankaccount;
    ?>
    @if (!empty($bankaccount))
      <h3>Dados para Depósito ou Transferência:</h3>
      <h4>Banco: {{ $bankaccount->bankname }} </h4>
      <h4>Agência: {{ $bankaccount->agency }} </h4>
      <h4>Conta-corrente: {{ $bankaccount->account }} </h4>
      <h4>A: {{ $bankaccount->customer }} </h4>
      <h4>CPF: {{ $bankaccount->cpf }} </h4>
    @endif
    {{-- @if !empty($bankaccount) --}}
  @endif
  {{-- @if !empty($billingitems) --}}
@endif
{{-- @if empty($cobranca) --}}
@endsection
