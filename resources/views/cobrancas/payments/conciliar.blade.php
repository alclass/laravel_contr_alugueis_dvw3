@extends('layouts.master')
@section('title')
    Conciliar Cobrança
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')
  <?php
    use Carbon\Carbon;
  ?>
  @if (empty($cobranca))
    <h3>Não foram encontrados itens de cobrança.</h3>
    <h3>Por favor, se isto estiver incorreto, entrar em contato.</h3>
  @endif

  @if (!empty($cobranca))
    <h2>Cobrança</h2>
    <h1>Aluguel e Encargos</h1>
    <br>
    <h3>Mês Ref.: {{ $cobranca->monthyeardateref }} </h3>
    <h3>Data de Vencimento: {{ $cobranca->duedate }}</h3>
    <?php
      $today = Carbon::today();
      $cobranca->load('billingitems');
    ?>
    <h3>Hoje: {{ $today }}</h3>
    <br>
    <br>

    @if (empty($cobranca->billingitems()->get()))
      <h3>Não foram encontrados itens de cobrança.</h3>
      <h3>Por favor, se isto estiver incorreto, entrar em contato.</h3>
    @endif

    @if (!empty($cobranca->billingitems()->get()))
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
          <td data-th="ref">   {{ $billingitem->format_monthyeardateref_as_m_slash_y() }} </td>
          <td data-th="valor"> {{ $billingitem->charged_value }} </td>
          <td data-th="repasse"> * </td>
        </tr>
      @endforeach
      </table>
      <h3>Total: {{ $cobranca->total }}</h3>
      @if ($cobranca->is_iptu_ano_quitado())
        <h5>(*) IPTU {{ $cobranca->monthyeardateref->year }} quitado</h5>
      @endif
      @if ($cobranca->contract != null and $cobranca->contract->repassar_condominio == false)
        <h5>(*) Condomínio sob pagamento direto, sem repasse acima.</h5>
      @endif
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
      @endif  {{-- @if !empty($bankaccount) --}}
    @endif  {{-- @if (!empty($cobranca->billingitems)) --}}

    @if ($cobranca->contract->imovel != null)
      <h5><a href="/condominios/{{ $cobranca->contract->imovel->id }}">Histórico das Tarifas de Condomínios.</a></h5>
    @endif

  @endif {{-- cobranca non empty --}}


<form name="conciliate_htmlform" class="" action="{{ route('cobranca.editargerar') }}" method="POST">
  {!! csrf_field() !!}
  Valor Recebido: <input type="hidden"  name="cobranca_id" value="{{ $cobranca->id }}"></input> <br>
  Valor Recebido: <input type="text"  name="valor_recebido" value="0.00"></input> <br>
  Data Recebido: <input type="text"  name="data_recebido" value="0.00"></input> <br>
  <input type="radio" name="meio_de_pagto" value="conta-corrente" checked>Conta-corrente</input>
  <input type="radio" name="meio_de_pagto" value="dinheiro">Dinheiro</input> <br>
  Dif. Créd.|Déb.: <input type="text" name="debito_ou_credito" value="0.00"></input> <br>
  <input type="submit" name="conciliate_button" value="conciliate_submit"></input> <br>
</form>
@endsection
