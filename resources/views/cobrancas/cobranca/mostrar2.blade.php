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
                        <strong>{{ $user->get_first_n_last_names() }}</strong>
                        <br>
                        {{ $imovel->get_street_address() }}
                        <br>

                        <abbr title="cep"></abbr>
                    </address>
                  </div>

                <div class="col-xs-6 col-sm-6 col-md-6 text-right">
                    <p>
                      Rio de Janeiro, {{ $today->format('d M Y') }}<br>
                      Ref.: <strong>{{ $cobranca->monthyeardateref->format('M/Y') }}</strong>
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="text-center">
                    <h1>Hollerith</h1>
                </div>
                </span>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Aluguel e Encargos</th>
                            <th>#</th>
                            <th class="text-center">Ref.</th>
                            <th class="text-center">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                      @foreach ($cobranca->billingitems()->get() as $billingitem)
                        <tr>
                            <td class="col-md-9"><em>{{ $billingitem->brief_description }}</em></h4></td>
                            <td class="col-md-1" style="text-align: center"> 1 </td>
                            <td class="col-md-1 text-center">{{ $billingitem->monthyeardateref->format('m-Y') }}</td>
                            <td class="col-md-1 text-center">{{ $billingitem->charged_value }}</td>
                        </tr>
                        @endforeach
                        <tr>
                            <td>   </td>
                            <td>   </td>
                            <td class="text-right"><h4><strong>Total: </strong></h4></td>
                            <td class="text-center text-danger"><h4><strong>{{ number_format($cobranca->get_total_value(),2) }}</strong></h4></td>
                        </tr>

                        <tr>
                            <td class="text-left">
                              <p>
                                Pagto até: <strong> {{ $cobranca->duedate->format('d/m/Y') }} </strong><br>
                                Agendar em {{ $cobranca->find_n_days_until_duedate() }} dias
                              </p>
                            </td>
                            <td>   </td>
                            <td>   </td>
                            <td class="text-center"> </td>
                        </tr>

                    </tbody>
                </table>
                <button type="button" class="btn btn-success btn-lg btn-block">
                    <span class="glyphicon glyphicon-chevron-right">Dados para Depósito/Transferência</span>
                </button>

                <p  class="text-center">
                  <strong> {{ $bankaccount->bankname }} </strong><br>
                  Agência: {{ $bankaccount->agency }}<br>
                  Conta-corrente: {{ $bankaccount->account }}<br>
                  A: Luiz Ferreira<br>
                  CPF 004651567-46<br>
                </p>

            </div>
        </div>
      </div>
</div>
@endsection
