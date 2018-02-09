@extends('layouts.master')
@section('title')
    Exibir Cobrança
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')
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
  <div class="row">
      <div class="text-center">
          <h2>Boleta da Cobrança Mensal</h2>
      </div>
      </span>
      <table class="table table-hover">
          <thead>
              <tr>
                  <th>Aluguel e Encargos</th>
                  <th>Ref.Q.</th>
                  <th class="text-center">Ref.Mês</th>
                  <th class="text-center">Valor</th>
              </tr>
          </thead>
          <tbody>
            @if(!empty($cobranca->billingitems))
            @foreach($cobranca->gen_createable_billingitems() as $billingitem)
              <p>1 2 3 ...</p>
              <tr>
                <em>{{ $tipocobrancastr }}</em>
                @if($cobrancatipo != null && $cobrancatipo->is_it_carried_debt())
                  <a href="{{ route('$billingitemroute', $cobranca->get_routeparams_toformerbill_asarray()) }}">
                    Cobr. Anterior
                  </a>
                @endif
              </td>
              <td class="col-md-1" style="text-align: center">
                {{ $billingitem->generate_ref_repr_for_cota_column() }}
              </td>
              <td class="col-md-1 text-center">




<form id="rendered-form">
<div class="rendered-form">
<div class="fb-date form-group field-date-1518106617049">
  <label for="date-1518106617049" class="fb-date-label">dateField</label>
  <input class="form-control" name="date-1518106617049" id="date-1518106617049" type="date">
</div>
</div>
</form>











                {{ $billingitem->monthrefdate->format('m-Y') }}</td>
              <td class="col-md-1 text-center">{{ $billingitem->value }}</td>
                  <td class="col-md-9">
                    <?php
                      $cobrancatipochar4id = 'n/a';
                      $cobrancatipobriefdescr = 'descr.';
                      $cobrancatipo = $billingitem->get_cobrancatipo();
                      if ($cobrancatipo != null) {
                        $cobrancatipochar4id = $cobrancatipo->char4id;
                        $cobrancatipobriefdescr = $cobrancatipo->brief_description;
                      }
                      $tipocobrancastr = $cobrancatipobriefdescr . ' (' . $cobrancatipochar4id . ')';
                    ?>


              </tr>
              @endforeach
              @endif
              <tr>
                  <td>   </td>
                  <td>   </td>
                  <td class="text-right"><h4><strong>Total: </strong></h4></td>
                  <td class="text-center text-danger">
                    <h4>
                      <strong>

  {{ number_format(floor($cobranca->get_total_value()),2) }}

                      </strong>
                    </h4>
                  </td>
              </tr>

              <tr>
                  <td class="text-left">
                    <p>
                      Vencimento: <strong> {{ $cobranca->duedate->format('d/m/Y') }} </strong><br>
                      <?php
                        $dias_faltando_str = 'n/a';
                        $dias_faltando = $cobranca->find_n_days_until_duedate_in_future();
                        if ($dias_faltando == 0) {
                          $dias_faltando_str = 'hoje';
                        }
                        else {
                          $dias_faltando_str = '' . $dias_faltando;
                        }
                      ?>
                      (Em {{ $dias_faltando_str }} dias)
                    </p>
                  </td>
                  <td>   </td>
                  <td>   </td>
                  <td class="text-center"> </td>
              </tr>

          </tbody>
      </table>

    <?php
      if ($bankaccount == null) {
        $bankaccount = \App\Models\Finance\BankAccount::get_default();
      }
    ?>

    @if(!empty($bankaccount))
      <button type="button" class="btn btn-success btn-lg btn-block">
        <span class="glyphicon glyphicon-chevron-right">
          Dados para Depósito/Transferência
        </span>
      </button>

      <p  class="text-center">
        <strong> {{ $bankaccount->bankname }} </strong><br>
        Agência: {{ $bankaccount->agency }}<br>
        Conta-corrente: {{ $bankaccount->account }}<br>
        A: {{ $bankaccount->customer }}<br>
        CPF {{ $bankaccount->cpf }}<br>
      </p>
    @endif

  </div>
  </div>
</div>
</div>
@endsection
