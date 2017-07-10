
@extends('layouts.master')
@section('title')
    Controle Locação
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')


<h1>Inquilino</h1>
<h4> {{ $user->name_first_last() }} </h4>
<h5> {{ $user->email }} </h5>

<h2>Imóvel</h2>
@foreach($user->imoveis as $imovel)
  <h4> Endereço:  <a href="{{ route('imovel.route', $imovel) }}">{{ $imovel->get_street_address() }} </a></h4>
  <h4> Aluguel: {{ $imovel->valor_aluguel }} </h4>
@endforeach

@endsection
