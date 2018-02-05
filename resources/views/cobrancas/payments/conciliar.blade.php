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
    <?php
      setlocale(LC_TIME, 'Brazil');
      Carbon::setLocale('pt');
      $today = Carbon::today();
      $cobranca->load('billingitems');
      $contract = $cobranca->contract;
      if ($contract == null) {
        throw Exception('contract is null inside conciliar.blade.php');
      }
      $imovel = $cobranca->get_imovel();
      $street_address = 'n/a';
      if ($imovel != null) {
        $street_address = $imovel->get_street_address();
      }
    ?>
    <h2>Pagamento com Depósito/Transferência</h2>
    <h1>Conciliação</h1>
    <h6>{{ $today->toFormattedDateString() }}</h6>
    <h5>Contrato: <a href="{{ route('contract', $contract->id) }}">{{ $street_address }} </a></h5>
    <h5>Pagto Ref.: <b>{{ $cobranca->monthrefdate->format('F/Y') }}</b>
    até {{ $cobranca->duedate->toFormattedDateString() }}</h5>
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
          <td data-th="ref">   {{ $billingitem->format_monthrefdate_as_m_slash_y() }} </td>
          <td data-th="valor"> {{ $billingitem->charged_value }} </td>
          <td data-th="repasse"> * </td>
        </tr>
      @endforeach
      </table>
      <h3>Total: {{ $cobranca->total }}</h3>
      @if ($cobranca->is_iptu_ano_quitado())
        <h5>(*) IPTU {{ $cobranca->monthrefdate->year }} quitado</h5>
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
  <input type="hidden"  name="cobranca_id" value="{{ $cobranca->id }}"></input> <br>
  Valor Recebido: <input type="text"  name="valor_recebido" value="0.00"></input> <br>
  Data Recebido: <input type="text"  name="data_recebido" value="0.00"></input> <br>
  <br>
  <input type="radio" name="meio_de_pagto" value="conta-corrente" checked>Conta-corrente</input>
  <input type="radio" name="meio_de_pagto" value="dinheiro">Dinheiro</input> <br>
  <br>
  Dif. Créd.|Déb.: <input type="text" name="mora_ou_credito" value="0.00"></input> <br>
  <input type="submit" name="conciliate_button" value="conciliate_submit"></input> <br>
</form>

<br>
<br>
<br>
<br>

@endsection
