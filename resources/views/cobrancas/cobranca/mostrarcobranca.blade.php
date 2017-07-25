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
    $today = Carbon::today();
    $bankaccount = null;
    $contract    = null;
    $imovel      = null;
    $imovel_endereco = 'n/a';
    if ($cobranca->contract != null) {
      $contract = $cobranca->contract;
      if ($contract->bankaccount != null) {
        $bankaccount = $contract->bankaccount;
      }
      if ($contract->imovel != null) {
        $imovel = $contract->imovel; //()->first();
        $imovel_endereco = $imovel->get_street_address();
      }
    }
  ?>
  <h4>Aluguel e Encargos</h4>
  <h7>{{ $today->format('d/m/Y') }}</h7>
  <h5>Imóvel: {{ $imovel_endereco }} </h5>
  <h5>Locº: {{ $contract->strlist_all_contractors() }} </h5>
  <br>
  @if ($cobranca->billingitems()->count()==0)
    <h3>Não foram encontrados itens de cobrança.</h3>
    <h3>Por favor, se isto estiver incorreto, entrar em contato.</h3>
  @endif
  @if ($cobranca->billingitems()->count() > 0)
    <div font-size="16">Holerith <div font-size="10">Ref. {{ $cobranca->monthyeardateref->format('M/Y') }}</div></div>
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
        <td data-th="ref">   {{ $billingitem->monthyeardateref->format('M-Y') }} </td>
        <td data-th="valor"> {{ $billingitem->charged_value }} </td>
        <td data-th="repasse"> * </td>
      </tr>
    @endforeach

    </table>
    <h5>Total: {{ $cobranca->total_value }}</h5>
    <h6>Vencimento: {{ $cobranca->duedate->format('d/m/Y') }}</h6>
    @endif  {{-- @if ( $cobranca->billingitems()->count() > 0 ) --}}

    <br>
    @if ($bankaccount != null)
      <h3>Dados para Depósito ou Transferência:</h3>
      <h4>Banco: {{ $bankaccount->bankname }} </h4>
      <h4>Agência: {{ $bankaccount->agency }} </h4>
      <h4>Conta-corrente: {{ $bankaccount->account }} </h4>
      <h4>A: {{ $bankaccount->customer }} </h4>
      <h4>CPF: {{ $bankaccount->cpf }} </h4>
    @endif  {{-- @if ($bankaccount != null) --}}

  @if ($cobranca->contract->imovel != null)
    <h5><a href="{{ route('condominio.tarifas', $cobranca->contract->imovel->id) }}">
      Histórico das Tarifas de Condomínios.</a></h5>
  @endif
  <br>
@endsection
