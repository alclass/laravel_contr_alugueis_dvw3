@extends('layouts.master')
@section('title')
    Exibir Cobrança
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')
<?php
  $form_date_ids = [];
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
  <div class="row">
      <div class="text-center">
          <h2>Boleta da Cobrança Mensal</h2>
      </div>
      </span>
      <table class="table table-hover">
          <thead>
              <tr>
                  <th>Aluguel e Encargos</th>
                  <th>Ref.Mês</th>
                  <th class="text-center">Ref.Q.</th>
                  <th class="text-center">Valor</th>
              </tr>
          </thead>
          <tbody>

    <form id="form_id" class="form-horizontal" action="{{ route('cobrancaeditarhttppostroute') }}" method="post">
    <fieldset>

    <!-- Form Name -->
    <legend>cobranca_form</legend>
            @foreach($cobranca->billingitems as $billingitem)
              <tr>
  <td class="col-md-9">
    <?php
      $cobrancatipochar4id = 'n/a';
      $cobrancatipobriefdescr = 'descr.';
      $cobrancatipo = $billingitem->cobrancatipo;
      if ($cobrancatipo != null) {
        $cobrancatipochar4id = $cobrancatipo->char4id;
        $cobrancatipobriefdescr = $cobrancatipo->brief_description;
      }
      $tipocobrancastr = $cobrancatipobriefdescr . ' (' . $cobrancatipochar4id . ')';
    ?>
    {{ $tipocobrancastr }}
    <input type="hidden"
      name="cobrancatipo4char-{{ $loop->iteration }}-fieldname"
      value="{{ $tipocobrancastr }}">
</td>
              <td class="col-md-1 text-center">

<?php
  $form_date_id    = 'date-' . $loop->iteration . '-id';
  $form_date_ids[] = $form_date_id;
?>

<div class="fb-date form-group field-date-{{ $loop->iteration }}">
  <label for="{{ $form_date_id }}" class="fb-date-label"></label>

  <input class="form-control" name="date-{{ $loop->iteration }}-fieldname"
    id="{{ $form_date_id }}" type="date">
</div>
              </td>
              <td class="col-md-1" style="text-align: center">
<input id="textinput" name="numberpart-{{ $loop->iteration }}-fieldname" placeholder="placeholder"
 class="form-control input-md" type="text"
 maxlength="1" value="{{ $billingitem->numberpart }}">
/
<input id="textinput" name="totalparts-{{ $loop->iteration }}-fieldname" placeholder="placeholder"
 class="form-control input-md"
 maxlength="1" type="text" value="{{ $billingitem->totalparts }}">
  @if($billingitem->cobrancatipo != null && $billingitem->cobrancatipo->is_it_carried_debt())
    <br>
    <a href="{{ route('billingitemroute', $cobranca->get_routeparams_toformerbill_asarray()) }}">
      Cobr. Anterior
    </a>
  @endif
              </td>
              <td>
  <?php
    $charged_value = number_format($billingitem->charged_value,2);
  ?>

  <input id="textinput" name="charged_value-{{ $loop->iteration }}-fieldname"
   class="form-control input-md" type="text" value="{{ $charged_value }}">
              </td>
              </tr>
              @endforeach
              <tr>
                  <td>   </td>
                  <td>   </td>
                  <td class="text-right"><h4><strong>Total: </strong></h4></td>
                  <td class="text-center text-danger">
                    <h4>
                      <strong>

  {{ number_format($cobranca->get_total_value(),2) }}

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

@if(count($form_date_ids)>0)
<div align="center">
  {{ csrf_field() }}
  <button type="submit" name="button">Enviar</button>
  <hr>
</div>
@endif

</fieldset>
</form>

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

<script type="text/javascript">

$( function() {

  @foreach($form_date_ids as $form_date_id)
    $('#{{ $form_date_id }}').val('{{ $cobranca->monthrefdate->format("Y-m-d") }}');
  @endforeach

});

</script>


@endsection
