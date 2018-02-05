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
    <div font-size="16">Holerith <div font-size="10">Ref. {{ $cobranca->monthrefdate->format('M/Y') }}</div></div>
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
        <td data-th="ref">   {{ $billingitem->monthrefdate->format('M-Y') }} </td>
        <td data-th="valor"> {{ $billingitem->charged_value }} </td>
        <td data-th="repasse"> * </td>
      </tr>
    @endforeach

    </table>
    <h5>Total: {{ $cobranca->get_total_value() }}</h5>
    <h6>Vencimento: {{ $cobranca->duedate->format('d/m/Y') }}</h6>
    @endif  {{-- @if ( $cobranca->billingitems()->count() > 0 ) --}}

    <br>
    <form class="form-horizontal" action="{{ route('cobranca.mensal.editar') }}">
    <fieldset>

    <!-- Form Name -->
    <legend>Gerar Itens de Cobrança</legend>
    <!-- Select Basic -->
    <div class="form-group">
      <label class="col-md-4 control-label" for="selectbasic">Item-cobrança</label>
      <div class="col-md-4">
        <select id="ref_type_select" name="ref_type_select" class="form-control">
          @foreach($cobranca->get_collection_cobrancatipos() as $cobrancatipo)
            <option value="{{ $cobrancatipo->char4id }}">
              {{ $cobrancatipo->brief_description }}
            </option>
          @endforeach
        </select>
      </div>
    </div>

    <!-- Multiple Radios (inline) -->
    <div class="form-group">
      <label class="col-md-4 control-label" for="radios">Tipo Referência (mês/cota)</label>
      <div class="col-md-4">
        <label class="radio-inline" for="radios-0">
          <input name="radios" id="radios-0" value="D" checked="checked" type="radio">
          ref. mês
        </label>
        <label class="radio-inline" for="radios-1">
          <input name="radios" id="radios-1" value="P" type="radio">
          ref. cota
        </label>
        <label class="radio-inline" for="radios-2">
          <input name="radios" id="radios-2" value="B" type="radio">
          ref. mês e cota
        </label>
      </div>
    </div>

    <!-- Text input-->
    <div class="form-group">
      <label class="col-md-4 control-label" for="charged_value">Valor</label>
      <div class="col-md-2">
      <input id="charged_value" name="charged_value"
        placeholder="{{ $cobranca->contract->current_rent_value }}" class="form-control input-md" required="" type="text">
      </div>
    </div>

    <!-- Text input-->
    <div class="form-group">
      <label class="col-md-4 control-label" for="monthref">Mês ref.</label>
      <div class="col-md-1">
      <input id="monthref" name="monthref"
        placeholder="{{ $cobranca->extract_month_from_monthrefdate() }}" class="form-control input-md" required="" type="text">
      </div>
      <label class="col-md-4 control-label" for="yearref">Ano ref.</label>
      <div class="col-md-1">
      <input id="yearref" name="yearref"
        placeholder="{{ $cobranca->extract_year_from_monthrefdate() }}" class="form-control input-md" required="" type="text">
      </div>
    </div>

    <!-- Button (Double) -->
    <div class="form-group">
      <label class="col-md-4 control-label" for="button1id">Double Button</label>
      <div class="col-md-8">
        <button id="button1id" name="button1id" class="btn btn-success">Good Button</button>
        <button id="button2id" name="button2id" class="btn btn-danger">Scary Button</button>
      </div>
    </div>

    </fieldset>
    </form>


  @if ($cobranca->contract->imovel != null)
    <h5><a href="{{ route('condominio.tarifas', $cobranca->contract->imovel->id) }}">
      Histórico das Tarifas de Condomínios.</a></h5>
  @endif
  <br>
@endsection
