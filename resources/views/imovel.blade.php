
@extends('layouts.master')
@section('title')
    Controle Locação
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/rwd-table.css') }}">
@endsection
@section('content')


<h1>Imóvel</h1>
<h4> {{ $imovel->get_street_address() }} </h4>
<h5> {{ $imovel->valor_aluguel  }} </h5>

<h2>Inquilino(s)</h2>
@foreach($imovel->users as $user)
  <h4> <a href="{{ route('user.route', $user) }}">{{ $user->name_first_last() }} </a></h4>
  <h5> {{ $user->email }} </h5>
@endforeach

@endsection
