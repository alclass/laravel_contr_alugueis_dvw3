@extends('layouts.master')
@section('title')
    Controle Locação
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/login.css') }}">
@endsection
@section('content')
<div class="login-page">
  <div class="form">
    <form class="register-form" action="">
      <input type="text" placeholder="name"/>
      <input type="password" placeholder="password"/>
      <input type="text" placeholder="email address"/>
      <button>create</button>
      <p class="message">Already registered? <a href="#">Sign In</a></p>
    </form>
    <form class="login-form">
      <input type="text" placeholder="username"/>
      <input type="password" placeholder="password"/>
      <button>login</button>
      <p class="message">Not registered? <a href="#">Create an account</a></p>
    </form>
  </div>
</div>
@endsection
@section('scripts')
<script type="javascript/text">
$('.message a').click(function(){
   $('form').animate({height: "toggle", opacity: "toggle"}, "slow");
});
</script>
@endsection
