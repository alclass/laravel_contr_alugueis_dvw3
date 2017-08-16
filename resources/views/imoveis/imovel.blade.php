
@extends('layouts.master')
@section('title')
    Exibir Imóvel
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')

  <h1>Exibir Imóvel</h1>
  <h4> {{ $imovel->get_street_address() }} </h4>

  @if ($contract != null)

    <h5> <a href="{{ route('contract', $contract->id) }}">Contrato Atual </a>
      vigência: {{ $contract->start_date->format('d/M/Y') }} a {{ $contract->get_end_date()->format('d/M/Y') }}</h5>
    <h5>Aluguel Valor Atual: {{ $contract->current_rent_value }}</h5>
    <h6>Próximo reajuste: {{ $next_reajust_date_formatted }} :: Daqui a {{ $contract->todays_diff_to_rent_value_next_reajust_date() }}</h6>

    @if ($contract->users->count() > 0)

      <h2>Inquilino(s): {{ $contract->users->count() }}</h2>

      @foreach($contract->users as $user)
        <h4> <a href="{{ route('persons.user', $user) }}">{{ $user->get_first_n_last_names() }} </a></h4>
        <h5> {{ $user->email }} </h5>
      @endforeach

    @else {{-- ie @if ($contract->users->count() > 0) --}}

      <h5> Não há inquilinos neste contrato.</h5>

    @endif {{-- @if ($contract->users->count() > 0) --}}

  @else {{-- ie @if ($contract == null) --}}

    <h5> Não há contratos no banco de dados relativos a este imóvel. </h5>

  @endif  {{-- @if ($contract != null) --}}

@endsection
