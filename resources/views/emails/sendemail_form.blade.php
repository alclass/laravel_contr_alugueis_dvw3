
@extends('layouts.master')
@section('title')
    Enviar Email
@endsection
@section('styles')

@endsection
@section('content')

  <h1>Enviar Email</h1>
  <h4>{{ $email_sent_msg }}</h4>
  <br>

  <form class="form-horizontal" action="{{ route('sendemail') }}">
    <fieldset>
    <div class="form-group">
      <label class="col-md-4 control-label" for="singlebutton">
        Teste
      </label>

      <label class="col-md-4 control-label" for="singlebutton">
        Teste
      </label>

      <input id="do_send_checkbox_id" name="do_send_checkbox" type="checkbox" value="1">

      <div class="col-md-4">
        <button id="do_send_button_id" name="do_send_button" class="btn btn-primary" value="Submit">
          Enviar Email
        </button>
      </div>
    </div>
  </fieldset>
  </form>
@endsection
