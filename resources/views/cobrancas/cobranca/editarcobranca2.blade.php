@extends('layouts.master')
@section('title')
    Exibir Cobrança
@endsection
@section('content')
<p>hi</p>

<?php
  $billingitems = $cobranca->gen_createable_billingitems();
?>
@foreach ($billingitems as $billingitem)



@endforeach

@endsection
