<?php
  $routeparams_toformerbill_asarray = $cobranca->get_routeparams_toformerbill_asarray();
  $routeparams_tonextbill_asarray   = $cobranca->get_routeparams_tonextbill_asarray();
?>

<div class="row">
  <div class="text-center">
    <h2>Boleta da Cobrança Mensal</h2>
  </div>

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
        <?php
          foreach($cobranca->billingitems as $billingitem) {
        ?>
          <tr>
            <?php
              $cobrancatipochar4id = 'n/a';
              $cobrancatipobriefdescr = 'descr.';
              if ($billingitem->cobrancatipo == null) {
                continue;
              }
              $cobrancatipochar4id = $billingitem->cobrancatipo->char4id;
              $cobrancatipobriefdescr = $billingitem->cobrancatipo->brief_description;
              $tipocobrancastr = $cobrancatipobriefdescr . ' (' . $cobrancatipochar4id . ')';
            ?>

              <td class="col-md-9">
                <em>{{ $tipocobrancastr }}</em>
                @if($billingitem->cobrancatipo != null && $billingitem->cobrancatipo->is_it_carried_debt() == true && !empty($routeparams_toformerbill_asarray))
                  <a href="{{ route('cobrancaviayearmonthimovapelroute', $routeparams_toformerbill_asarray) }}">
                    Cobr. Anterior
                  </a>
                @endif
              </td>
              <td class="col-md-1" style="text-align: center">
                {{ $billingitem->generate_ref_repr_for_cota_column() }}
              </td>
              <td class="col-md-1 text-center">{{ $billingitem->monthrefdate->format('m-Y') }}</td>
              <td class="col-md-1 text-center">{{ $billingitem->charged_value }}</td>
          </tr>
          <?php
            } // ends 'foreach($cobranca->billingitems as $billingitem) {
          ?>
          <tr>
              <td>   </td>
              <td>   </td>
              <td class="text-right"><h4><strong>Total: </strong></h4></td>
              <td class="text-center text-danger">
                <h4>
                  <strong>
                    {{ number_format($cobranca->totalvalue, 2) }}
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

@if($bankaccount!=null)
  <button type="button" class="btn btn-success btn-lg btn-block">
      <span class="glyphicon glyphicon-chevron-right">
        Dados para Depósito/Transferência
      </span>
  </button>


  <p  class="text-center">
    <strong> {{ $bankaccount->bankname }} </strong><br>
    Agência: {{ $bankaccount->agency }}<br>
    Conta-corrente: {{ $bankaccount->account }}<br>
    A: {{ $bankaccount->customer }} <br>
    CPF {{ $bankaccount->cpf }} <br>
  </p>

  <hr>
  <p>
    Cobr. ID {{ $cobranca->id }}
    <a href="{{ route('cobrancaeditarhttpgetroute', $cobranca->urlrouteparamsasarray) }}">
      edit
    </a>
    ||
    <?php
      if (!session()->has('cobranca')) {
        session()->put('cobranca', $cobranca);
      }
      $scobranca = session()->get('cobranca');
      if ($scobranca->id != $cobranca->id) {
        session()->put('cobranca', $cobranca);
      }
    ?>
    <form id="deletecobrancaformid" action="{{ route('cobrancadeletehttppostroute') }}" method="post">
      {{ csrf_field() }}
      <a href="#" class="confirmdeleteeventclass">
        delete
      </a>
      <br>
      <button type="button" name="button">
        delete
      </button>
  </form>
  </p>

  <table>
    <tr>
      <td>
        @if(!empty($routeparams_toformerbill_asarray))
        <a href="{{ route('cobrancaviayearmonthimovapelroute', $routeparams_toformerbill_asarray) }}">
          Próx. Anterior
        </a>
        @else
          Primeira cobr. no DB
        @endif
      </td>
<td>
  ||
</td>
      <td>
        @if(!empty($routeparams_tonextbill_asarray))
        <a href="{{ route('cobrancaviayearmonthimovapelroute', $routeparams_tonextbill_asarray) }}">
          Próx. Cobr.
        </a>
        @else
          Última cobr. no DB
        @endif
      </td>

    </tr>
  </table>

<script type="text/javascript">
$(document).on('click', '.confirmdeleteeventclass', function(e) {
  var answer = confirm('Tem certeza que quer deletá-lo?');
  console.log('passed the confirm line');
  if (answer == true) {
    document.getElementById('deletecobrancaformid').submit();
  }
});
</script>

@endif
